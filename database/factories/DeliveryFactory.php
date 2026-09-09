<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Delivery>
 *
 * vehicle_id/driver_id are left null by default (both nullable) so tests
 * that don't care about them don't need to pull in the Vehicle/Driver
 * factories.
 */
class DeliveryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'delivery_date' => Carbon::today()->toDateString(),
            'status' => DeliveryStatus::Dispatched,
            'external_reference' => 'SFA-DEL-'.now()->format('Ymd').'-'.fake()->unique()->numerify('######'),
            'sync_status' => TallyRecordSyncStatus::NotSynced,
        ];
    }
}
