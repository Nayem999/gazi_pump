<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Depot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Depot>
 */
class DepotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Depot '.fake()->unique()->numberBetween(1, 999),
            'code' => 'DEP-'.fake()->unique()->numerify('###'),
            'address' => fake()->address(),
            'status' => true,
        ];
    }
}
