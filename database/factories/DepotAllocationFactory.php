<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AllocationStatus;
use App\Models\Depot;
use App\Models\DepotAllocation;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DepotAllocation>
 */
class DepotAllocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'depot_id' => Depot::factory(),
            'requested_qty' => 10,
            'allocated_qty' => 10,
            'allocation_status' => AllocationStatus::Allocated,
            'is_alternative_depot' => false,
        ];
    }
}
