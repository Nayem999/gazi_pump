<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Backfills the last 30 days of manual sales entries for every Sales
 * Executive, so the Sales Entry history view and the future Target/
 * Achievement engine (Phase 10) have realistic monthly volume right after a
 * fresh seed. Sales — unlike Attendance/GPS/Visits — happen any day of the
 * week, so weekends are not skipped here. Each sale can cover 1-3 different
 * products, mirroring a real field rep selling a small basket per stop.
 */
class SalesEntrySeeder extends Seeder
{
    private const DAYS = 30;

    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();
        $customersByTerritory = Customer::all()->groupBy('territory_id');
        $products = Product::where('status', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        foreach ($executives as $executive) {
            $pool = $customersByTerritory->get($executive->territory_id) ?? collect();
            if ($pool->isEmpty()) {
                $pool = Customer::inRandomOrder()->limit(5)->get();
            }

            if ($pool->isEmpty()) {
                continue;
            }

            for ($daysAgo = 0; $daysAgo < self::DAYS; $daysAgo++) {
                $date = Carbon::today()->subDays($daysAgo);

                $salesToday = random_int(0, 4);
                for ($i = 0; $i < $salesToday; $i++) {
                    $this->createSale($executive, $pool->random(), $products, $date);
                }
            }
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createSale(User $executive, Customer $customer, Collection $products, Carbon $date): void
    {
        $lineCount = random_int(1, 3);
        $lineProducts = $products->random(min($lineCount, $products->count()));

        $salesEntry = SalesEntry::create([
            'user_id' => $executive->id,
            'customer_id' => $customer->id,
            'sale_date' => $date->toDateString(),
            'total_amount' => 0,
        ]);

        $total = 0.0;

        foreach ($lineProducts as $product) {
            $quantity = random_int(1, 20);
            $unitPrice = (float) $product->price;
            $subtotal = $quantity * $unitPrice;
            $discountPercent = random_int(1, 100) <= 30 ? random_int(1, 15) : 0;
            $discountAmount = round($subtotal * $discountPercent / 100, 2);
            $lineTotal = round($subtotal - $discountAmount, 2);

            $salesEntry->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_amount' => $lineTotal,
            ]);

            $total += $lineTotal;
        }

        $salesEntry->update(['total_amount' => $total]);
    }
}
