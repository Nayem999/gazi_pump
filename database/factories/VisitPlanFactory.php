<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisitPlanStatus;
use App\Models\Dealer;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<VisitPlan>
 */
class VisitPlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dealer_id' => Dealer::factory(),
            'planned_date' => Carbon::today()->toDateString(),
            'status' => VisitPlanStatus::Planned,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => VisitPlanStatus::Completed]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => VisitPlanStatus::Cancelled]);
    }
}
