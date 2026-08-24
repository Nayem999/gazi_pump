<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Seeds exactly 5 orders (one per sample dealer, this month) spread across
 * the 2 sample Sales Executives, each covering 1-3 different real products
 * from ProductSeeder's catalog — mirrors a real field rep selling a small
 * basket per stop.
 */
class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();
        $dealers = Dealer::orderBy('id')->get();
        $products = Product::where('status', true)->get();

        if ($executives->isEmpty() || $dealers->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($dealers as $i => $dealer) {
            $executive = $executives->get($i % $executives->count());
            $date = Carbon::today()->subDays($i);

            $this->createOrder($executive, $dealer, $products, $date);
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createOrder(User $executive, Dealer $dealer, Collection $products, Carbon $date): void
    {
        $lineCount = random_int(1, 3);
        $lineProducts = $products->random(min($lineCount, $products->count()));

        $order = Order::create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => $date->toDateString(),
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

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_amount' => $lineTotal,
            ]);

            $total += $lineTotal;
        }

        $order->update(['total_amount' => $total]);
    }
}
