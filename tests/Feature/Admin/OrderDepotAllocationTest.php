<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AllocationStatus;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 3: a manager picking a depot for one order line from the Order
 * detail page (spec §12's "Select Alternative Depot" step).
 */
class OrderDepotAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function orderWithItem(Product $product, int $quantity = 5): array
    {
        $order = Order::factory()->create(['dealer_id' => Dealer::factory()->create()->id, 'status' => 'pending', 'total_amount' => 500]);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        return [$order, $item];
    }

    public function test_the_depot_allocation_card_renders_on_the_order_show_page(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 20, 'reserved_qty' => 0]);
        [$order] = $this->orderWithItem($product);

        $response = $this->actingAs($manager)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Depot Allocation');
        $response->assertSee($depot->name);
    }

    public function test_a_manager_can_allocate_a_depot_to_an_order_line(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 20, 'reserved_qty' => 0]);
        [$order, $item] = $this->orderWithItem($product, 5);

        $response = $this->actingAs($manager)->post(route('orders.items.allocate', [$order, $item]), [
            'depot_id' => $depot->id,
            'quantity' => 5,
            'is_alternative_depot' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('depot_allocations', [
            'order_item_id' => $item->id,
            'depot_id' => $depot->id,
            'allocated_qty' => 5,
            'is_alternative_depot' => 1,
            'allocation_status' => AllocationStatus::Allocated->value,
        ]);

        $stock = ProductStock::where('depot_id', $depot->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(5.0, (float) $stock->reserved_qty);
    }

    public function test_allocating_more_than_sellable_stock_is_rejected(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 3, 'reserved_qty' => 0]);
        [$order, $item] = $this->orderWithItem($product, 5);

        $this->actingAs($manager)->post(route('orders.items.allocate', [$order, $item]), [
            'depot_id' => $depot->id,
            'quantity' => 5,
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('depot_allocations', ['order_item_id' => $item->id]);
    }

    public function test_an_order_item_belonging_to_a_different_order_returns_not_found(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        Depot::factory()->create();
        [$order] = $this->orderWithItem($product);
        [, $otherItem] = $this->orderWithItem($product);

        $this->actingAs($manager)->post(route('orders.items.allocate', [$order, $otherItem]), [
            'depot_id' => Depot::factory()->create()->id,
            'quantity' => 1,
        ])->assertNotFound();
    }

    public function test_a_sales_executive_without_edit_permission_is_forbidden(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 20, 'reserved_qty' => 0]);
        [$order, $item] = $this->orderWithItem($product);

        $this->actingAs($executive)->post(route('orders.items.allocate', [$order, $item]), [
            'depot_id' => $depot->id,
            'quantity' => 5,
        ])->assertForbidden();
    }
}
