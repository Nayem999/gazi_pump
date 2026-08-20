<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Territory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Territory>
 */
class TerritoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->streetName().' Territory',
            'code' => 'TER-'.fake()->unique()->numerify('####'),
            'manager_id' => null,
            'center_lat' => fake()->latitude(20.5, 26.7),
            'center_lng' => fake()->longitude(88.0, 92.7),
            'boundary' => null,
            'status' => true,
        ];
    }
}
