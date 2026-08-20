<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SalesTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesTeam>
 */
class SalesTeamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Team '.fake()->unique()->numberBetween(1, 999),
            'code' => 'TEAM-'.fake()->unique()->numerify('##'),
            'description' => fake()->sentence(),
            'status' => true,
        ];
    }
}
