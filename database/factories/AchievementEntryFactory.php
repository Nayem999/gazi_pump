<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Models\AchievementEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<AchievementEntry>
 */
class AchievementEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entry_date' => Carbon::today()->toDateString(),
            'order_value_achieved' => fake()->randomFloat(2, 5000, 50000),
            'collection_achieved' => fake()->randomFloat(2, 3000, 30000),
            'quantity_achieved' => fake()->numberBetween(1, 20),
            'status' => ApprovalStatus::Pending->value,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ApprovalStatus::Approved->value,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}
