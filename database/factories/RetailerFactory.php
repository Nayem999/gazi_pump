<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dealer;
use App\Models\Retailer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Retailer>
 */
class RetailerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dealer_id' => Dealer::factory(),
            'name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'image' => null,
            'shipping_address' => fake()->address(),
            'status' => true,
        ];
    }
}
