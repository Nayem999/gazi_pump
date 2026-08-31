<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ApprovalStatus;
use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\AchievementEntry;
use App\Models\Target;
use Illuminate\Support\Carbon;

/**
 * Computes one Target's actual performance against its Sales Executive's
 * approved daily AchievementEntry rows for its user/month/year, and upserts
 * the single Achievement row that represents it. Pure and synchronous —
 * RecalculateAchievementsJob is the queueable wrapper around this for use
 * from HTTP/CLI callers.
 */
class CalculateAchievementAction
{
    public function __invoke(Target $target): Achievement
    {
        $achieved = AchievementEntry::where('user_id', $target->user_id)
            ->where('status', ApprovalStatus::Approved)
            ->whereYear('entry_date', $target->year)
            ->whereMonth('entry_date', $target->month)
            ->selectRaw('SUM(order_value_achieved) as order_achieved, SUM(collection_achieved) as collection_achieved, SUM(quantity_achieved) as quantity_achieved')
            ->first();

        $orderAchieved = (float) ($achieved->order_achieved ?? 0);
        $collectionAchieved = (float) ($achieved->collection_achieved ?? 0);
        $quantityAchieved = (int) ($achieved->quantity_achieved ?? 0);

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
