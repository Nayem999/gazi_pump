<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AchievementEntry;
use App\Models\AchievementItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AchievementItem>
 */
class AchievementItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'achievement_entry_id' => AchievementEntry::factory(),
            'product_id' => Product::factory(),
            'order_achieved' => fake()->randomFloat(2, 1000, 10000),
            'collection_achieved' => fake()->randomFloat(2, 1000, 10000),
            'quantity_achieved' => fake()->numberBetween(1, 10),
        ];
    }
}
