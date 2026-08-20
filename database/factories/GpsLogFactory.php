<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GpsLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<GpsLog>
 */
class GpsLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lat' => fake()->latitude(23.7, 23.85),
            'lng' => fake()->longitude(90.35, 90.45),
            'recorded_at' => Carbon::now(),
            'accuracy' => fake()->randomFloat(2, 3, 50),
            'speed' => fake()->randomFloat(2, 0, 60),
            'battery_level' => fake()->numberBetween(15, 100),
        ];
    }
}
