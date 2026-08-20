<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\Target;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salesPct = fake()->randomFloat(2, 0, 150);
        $collectionPct = fake()->randomFloat(2, 0, 150);
        $quantityPct = fake()->randomFloat(2, 0, 150);
        $overallPct = round(($salesPct + $collectionPct + $quantityPct) / 3, 2);

        return [
            'target_id' => Target::factory(),
            'sales_achieved' => fake()->randomFloat(2, 0, 2000000),
            'collection_achieved' => fake()->randomFloat(2, 0, 1500000),
            'quantity_achieved' => fake()->numberBetween(0, 300),
            'sales_pct' => $salesPct,
            'collection_pct' => $collectionPct,
            'quantity_pct' => $quantityPct,
            'overall_pct' => $overallPct,
            'grade' => match (true) {
                $overallPct >= 90 => PerformanceGrade::A,
                $overallPct >= 75 => PerformanceGrade::B,
                $overallPct >= 60 => PerformanceGrade::C,
                $overallPct >= 40 => PerformanceGrade::D,
                default => PerformanceGrade::F,
            },
            'calculated_at' => Carbon::now(),
        ];
    }
}
