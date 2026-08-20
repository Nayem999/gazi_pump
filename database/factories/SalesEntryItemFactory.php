<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\SalesEntryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesEntryItem>
 */
class SalesEntryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->randomFloat(2, 50, 500);
        $discountAmount = 0.0;

        return [
            'sales_entry_id' => SalesEntry::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'total_amount' => ($quantity * $unitPrice) - $discountAmount,
        ];
    }
}
