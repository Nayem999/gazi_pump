<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Target>
 */
class TargetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
            'sales_value_target' => fake()->randomFloat(2, 500000, 2000000),
            'collection_target' => fake()->randomFloat(2, 300000, 1500000),
            'quantity_target' => fake()->numberBetween(50, 300),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
