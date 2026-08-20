<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic product catalog for Gazi Pump (the company's own single
 * brand — there is no multi-brand catalog): a handful of categories, then a
 * spread of products across each one.
 */
class ProductSeeder extends Seeder
{
    private const CATEGORIES = [
        ['name' => 'Submersible Pumps', 'code' => 'CAT-001'],
        ['name' => 'Surface Pumps', 'code' => 'CAT-002'],
        ['name' => 'Motors', 'code' => 'CAT-003'],
        ['name' => 'Spare Parts', 'code' => 'CAT-004'],
        ['name' => 'Accessories', 'code' => 'CAT-005'],
    ];

    public function run(): void
    {
        $categories = collect(self::CATEGORIES)->map(
            fn (array $data) => ProductCategory::factory()->create($data)
        );

        $skuSequence = 1;

        $categories->each(function (ProductCategory $category) use (&$skuSequence) {
            for ($i = 0; $i < 6; $i++) {
                Product::factory()->create([
                    'category_id' => $category->id,
                    'sku' => 'SKU-'.str_pad((string) $skuSequence++, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }
}
