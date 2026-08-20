<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\CollectionEntry;
use App\Models\SalesEntry;
use App\Models\SalesEntryItem;
use App\Models\Target;
use Illuminate\Support\Carbon;

/**
 * Computes one Target's actual performance against real Sales/Collection
 * Entry data for its user/month/year, and upserts the single Achievement
 * row that represents it. Pure and synchronous — RecalculateAchievementsJob
 * is the queueable wrapper around this for use from HTTP/CLI callers.
 */
class CalculateAchievementAction
{
    public function __invoke(Target $target): Achievement
    {
        $salesAchieved = (float) SalesEntry::where('user_id', $target->user_id)
            ->whereYear('sale_date', $target->year)
            ->whereMonth('sale_date', $target->month)
            ->sum('total_amount');

        $collectionAchieved = (float) CollectionEntry::where('user_id', $target->user_id)
            ->whereYear('collection_date', $target->year)
            ->whereMonth('collection_date', $target->month)
            ->sum('amount');

        $quantityAchieved = (int) SalesEntryItem::whereHas('salesEntry', function ($query) use ($target) {
            $query->where('user_id', $target->user_id)
                ->whereYear('sale_date', $target->year)
                ->whereMonth('sale_date', $target->month);
        })->sum('quantity');

        $salesPct = $this->percentOf($salesAchieved, (float) $target->sales_value_target);
        $collectionPct = $this->percentOf($collectionAchieved, (float) $target->collection_target);
        $quantityPct = $this->percentOf((float) $quantityAchieved, (float) $target->quantity_target);
        $overallPct = round(($salesPct + $collectionPct + $quantityPct) / 3, 2);

        return Achievement::updateOrCreate(
            ['target_id' => $target->id],
            [
                'sales_achieved' => $salesAchieved,
                'collection_achieved' => $collectionAchieved,
                'quantity_achieved' => $quantityAchieved,
                'sales_pct' => $salesPct,
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
