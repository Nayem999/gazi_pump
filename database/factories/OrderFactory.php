<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Order>
 *
 * Produces a standalone header row with a plausible total_amount, for tests
 * that only care about the aggregate (e.g. Collection Entry's outstanding
 * balance). The real create/update flow always attaches items through
 * OrderService — this factory does not create any.
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dealer_id' => Dealer::factory(),
            'order_date' => Carbon::today()->toDateString(),
            'total_amount' => fake()->randomFloat(2, 100, 5000),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
