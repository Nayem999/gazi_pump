<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\CollectionEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Target;
use Illuminate\Support\Carbon;

/**
 * Computes one Target's actual performance against real Order/Collection
 * Entry data for its user/month/year, and upserts the single Achievement
 * row that represents it. Pure and synchronous — RecalculateAchievementsJob
 * is the queueable wrapper around this for use from HTTP/CLI callers.
 */
class CalculateAchievementAction
{
    public function __invoke(Target $target): Achievement
    {
        $orderAchieved = (float) Order::where('user_id', $target->user_id)
            ->whereYear('order_date', $target->year)
            ->whereMonth('order_date', $target->month)
            ->sum('total_amount');

        $collectionAchieved = (float) CollectionEntry::where('user_id', $target->user_id)
            ->whereYear('collection_date', $target->year)
            ->whereMonth('collection_date', $target->month)
            ->sum('amount');

        $quantityAchieved = (int) OrderItem::whereHas('order', function ($query) use ($target) {
            $query->where('user_id', $target->user_id)
                ->whereYear('order_date', $target->year)
                ->whereMonth('order_date', $target->month);
        })->sum('quantity');

        $orderPct = $this->percentOf($orderAchieved, (float) $target->order_value_target);
        $collectionPct = $this->percentOf($collectionAchieved, (float) $target->collection_target);
        $quantityPct = $this->percentOf((float) $quantityAchieved, (float) $target->quantity_target);
        $overallPct = round(($orderPct + $collectionPct + $quantityPct) / 3, 2);

        return Achievement::updateOrCreate(
            ['target_id' => $target->id],
            [
                'order_achieved' => $orderAchieved,
                'collection_achieved' => $collectionAchieved,
                'quantity_achieved' => $quantityAchieved,
                'order_pct' => $orderPct,
                'collection_pct' => $collectionPct,
                'quantity_pct' => $quantityPct,
                'overall_pct' => $overallPct,
                'grade' => $this->gradeFor($overallPct)->value,
                'calculated_at' => Carbon::now(),
            ],
        );
    }

    private function percentOf(float $achieved, float $target): float
    {
        if ($target <= 0) {
            return 0.0;
        }

        return round(($achieved / $target) * 100, 2);
    }

    private function gradeFor(float $overallPct): PerformanceGrade
    {
        foreach (config('sfa.targets.grade_thresholds') as $grade => $minPct) {
            if ($overallPct >= $minPct) {
                return PerformanceGrade::from($grade);
            }
        }

        return PerformanceGrade::F;
    }
}
