<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStock>
 */
class ProductStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'depot_id' => Depot::factory(),
            'product_id' => Product::factory(),
            'opening_qty' => 0,
            'in_qty' => 100,
            'out_qty' => 0,
            'closing_qty' => 100,
            'available_qty' => 100,
            'reserved_qty' => 0,
            'allocated_qty' => 0,
            'last_synced_at' => now(),
        ];
    }
}
