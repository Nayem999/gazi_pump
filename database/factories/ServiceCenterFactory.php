<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCenter>
 */
class ServiceCenterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Service Center',
            'address' => fake()->address(),
            'phone' => fake()->numerify('01#########'),
            'lat' => fake()->latitude(20.7, 26.6),
            'lng' => fake()->longitude(88.0, 92.7),
            'is_active' => true,
        ];
    }
}
