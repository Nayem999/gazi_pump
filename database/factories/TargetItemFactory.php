<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Target;
use App\Models\TargetItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TargetItem>
 */
class TargetItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'target_id' => Target::factory(),
            'product_id' => Product::factory(),
            'order_target' => fake()->randomFloat(2, 1000, 50000),
            'collection_target' => fake()->randomFloat(2, 1000, 50000),
            'quantity_target' => fake()->numberBetween(1, 200),
        ];
    }
}
