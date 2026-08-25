<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Day',
            'date' => fake()->unique()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d'),
            'description' => fake()->sentence(),
            'status' => true,
        ];
    }
}
