<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PerformanceGrade;
use App\Models\Target;
use App\Models\Territory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Backs the Territory Map. `markers()` is deliberately cheap — just the
 * plot-relevant columns for every territory (now real Union Council
 * boundaries, 5,000+ rows) — so the map loads fast with no per-territory
 * performance aggregation. That expensive join (plus the achievement-grade
 * lookup, unchanged from the original grade-driven map) only runs for the
 * one territory a user actually clicks, via `performanceFor()`.
 */
class TerritoryMapService
{
    public function __construct(private readonly ReportService $reports) {}

    /**
     * @return Collection<int, Territory>
     */
    public function markers(): Collection
    {
        return Territory::query()
            ->select(['id', 'name', 'code', 'center_lat', 'center_lng', 'boundary', 'manager_id'])
            ->withCount('users')
            ->get();
    }

    public function performanceFor(Territory $territory, int $month, int $year): object
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $performance = $this->reports->territoryPerformance([
            'date_from' => $periodStart->toDateString(),
            'date_to' => $periodEnd->toDateString(),
            'territory_id' => $territory->id,
        ])->first();

        $avgPct = Target::query()
            ->join('users', 'users.id', '=', 'targets.user_id')
            ->join('achievements', 'achievements.target_id', '=', 'targets.id')
            ->where('targets.month', $month)
            ->where('targets.year', $year)
            ->where('users.territory_id', $territory->id)
            ->avg('achievements.overall_pct');

        $avgPct = $avgPct !== null ? (float) $avgPct : null;

        $performance->avg_achievement_pct = $avgPct;
        $performance->grade = $avgPct !== null ? $this->gradeFor($avgPct) : null;

        return $performance;
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
