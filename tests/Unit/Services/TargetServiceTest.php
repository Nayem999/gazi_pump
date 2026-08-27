<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Target;
use App\Models\User;
use App\Services\TargetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TargetService
    {
        return app(TargetService::class);
    }

    public function test_product_achievements_sums_order_items_for_the_targets_period(): void
    {
        $executive = User::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        $target->items()->create(['product_id' => $productA->id, 'order_target' => 1000, 'collection_target' => 500, 'quantity_target' => 10]);
        $target->items()->create(['product_id' => $productB->id, 'order_target' => 2000, 'collection_target' => 1000, 'quantity_target' => 20]);

        $order = Order::factory()->create(['user_id' => $executive->id, 'order_date' => '2026-08-15', 'total_amount' => 900]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productA->id, 'quantity' => 4, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 400]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $productB->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $rows = $this->service()->productAchievements($target->load('items.product'));

        $rowA = $rows->firstWhere('product.id', $productA->id);
        $this->assertSame(400.0, $rowA->order_achieved);
        $this->assertSame(4, $rowA->quantity_achieved);
        $this->assertSame(40.0, $rowA->order_pct);
        $this->assertSame(40.0, $rowA->quantity_pct);

        $rowB = $rows->firstWhere('product.id', $productB->id);
        $this->assertSame(500.0, $rowB->order_achieved);
        $this->assertSame(5, $rowB->quantity_achieved);
    }

    public function test_product_achievements_excludes_orders_outside_the_targets_period(): void
    {
        $executive = User::factory()->create();
        $product = Product::factory()->create();

        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        $target->items()->create(['product_id' => $product->id, 'order_target' => 1000, 'collection_target' => 500, 'quantity_target' => 10]);

        // Same executive/product, but a different month — must not count.
        $order = Order::factory()->create(['user_id' => $executive->id, 'order_date' => '2026-07-15', 'total_amount' => 900]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 9, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 900]);

        $rows = $this->service()->productAchievements($target->load('items.product'));

        $this->assertSame(0.0, $rows->first()->order_achieved);
        $this->assertSame(0, $rows->first()->quantity_achieved);
    }

    public function test_product_achievements_is_empty_for_a_non_product_wise_target(): void
    {
        $target = Target::factory()->create();

        $rows = $this->service()->productAchievements($target->load('items.product'));

        $this->assertCount(0, $rows);
    }
}
