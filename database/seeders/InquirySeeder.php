<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InquiryStatus;
use App\Models\CustomerAccount;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        $accounts = CustomerAccount::all();
        $products = Product::all();

        // Anonymous inquiries submitted via the public Contact Us form.
        Inquiry::factory()->count(5)->create(['status' => InquiryStatus::New]);

        // Inquiries from logged-in customers, some about a specific product.
        foreach (range(1, 8) as $i) {
            Inquiry::factory()->create([
                'customer_account_id' => $accounts->random()->id,
                'product_id' => $products->isNotEmpty() && $i % 2 === 0 ? $products->random()->id : null,
                'status' => fake()->randomElement(InquiryStatus::cases()),
            ]);
        }
    }
}
