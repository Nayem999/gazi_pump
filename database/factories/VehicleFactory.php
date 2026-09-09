<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => 'VEH-'.fake()->unique()->numerify('####'),
            'type' => fake()->randomElement(['Truck', 'Van', 'Pickup']),
            'capacity' => fake()->randomElement(['1 Ton', '2 Ton', '5 Ton']),
            'status' => true,
        ];
    }
}
