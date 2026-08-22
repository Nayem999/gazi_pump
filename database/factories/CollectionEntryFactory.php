<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CollectionEntry>
 */
class CollectionEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method = fake()->randomElement(PaymentMethod::cases());

        return [
            'user_id' => User::factory(),
            'dealer_id' => Dealer::factory(),
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => fake()->randomFloat(2, 500, 20000),
            'payment_method' => $method,
            'reference_no' => $method === PaymentMethod::Cash ? null : strtoupper(fake()->bothify('REF-#####??')),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
