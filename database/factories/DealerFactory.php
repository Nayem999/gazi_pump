<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dealer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dealer>
 */
class DealerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dealer_code' => 'CUST-'.fake()->unique()->numerify('#####'),
            'name' => fake()->company(),
            'phone' => fake()->numerify('01#########'),
            'email' => fake()->boolean(60) ? fake()->unique()->companyEmail() : null,
            'address' => fake()->address(),
            'gps_lat' => fake()->latitude(20.5, 26.7),
            'gps_lng' => fake()->longitude(88.0, 92.7),
            'territory_id' => null,
            'status' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => false]);
    }
}
