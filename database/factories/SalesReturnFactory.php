<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SalesReturnStatus;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesReturn>
 */
class SalesReturnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'dealer_id' => Dealer::factory(),
            'user_id' => User::factory(),
            'status' => SalesReturnStatus::Requested,
            'reason' => fake()->sentence(),
            'external_reference' => 'SFA-SR-'.now()->format('Ymd').'-'.fake()->unique()->numerify('######'),
            'sync_status' => TallyRecordSyncStatus::NotSynced,
        ];
    }
}
