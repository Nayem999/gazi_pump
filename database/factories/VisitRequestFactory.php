<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisitRequestStatus;
use App\Models\CustomerAccount;
use App\Models\VisitRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitRequest>
 */
class VisitRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_account_id' => CustomerAccount::factory(),
            'preferred_date' => fake()->dateTimeBetween('+1 day', '+21 days')->format('Y-m-d'),
            'address' => fake()->address(),
            'message' => fake()->optional()->sentence(),
            'status' => VisitRequestStatus::Pending,
        ];
    }
}
