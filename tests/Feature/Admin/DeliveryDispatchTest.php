<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Dealer;
use App\Models\Delivery;
use App\Models\Depot;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DepotAllocationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 4: dispatching a fully depot-allocated, approved order — and its
 * push toward Tally as a Delivery Note voucher (mirrors Order/Collection
 * Entry's own guard-then-enqueue shape, see DeliveryService).
 */
class DeliveryDispatchTest extends TestCase
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

    private function fullyAllocatedApprovedOrder(Dealer $dealer, Product $product, int $quantity = 5): Order
    {
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 50, 'reserved_qty' => 0]);

        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'status' => 'approved', 'total_amount' => 500]);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        app(DepotAllocationService::class)->allocateManually($item, $depot->id, $quantity);

        return $order;
    }

    public function test_dispatch_is_rejected_for_an_order_that_is_not_yet_approved(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'status' => 'pending']);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $this->actingAs($manager)->post(route('orders.dispatch', $order), [
            'delivery_date' => now()->toDateString(),
        ])->assertSessionHasErrors('order');

        $this->assertSame(0, Delivery::count());
    }

    public function test_dispatch_is_rejected_when_a_line_is_not_fully_depot_allocated(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'status' => 'approved']);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $this->actingAs($manager)->post(route('orders.dispatch', $order), [
            'delivery_date' => now()->toDateString(),
        ])->assertSessionHasErrors('order');

        $this->assertSame(0, Delivery::count());
    }

    public function test_a_fully_allocated_approved_order_can_be_dispatched_and_enqueues_a_tally_sync_job(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->fullyAllocatedApprovedOrder($dealer, $product);
        $vehicle = Vehicle::factory()->create();
        $driver = Driver::factory()->create();

        $response = $this->actingAs($manager)->post(route('orders.dispatch', $order), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'delivery_date' => now()->toDateString(),
            'remarks' => 'Handle with care',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(DeliveryStatus::Dispatched, $delivery->status);
        $this->assertMatchesRegularExpression('/^SFA-DEL-\d{8}-\d{6}$/', $delivery->external_reference);
        $this->assertSame(TallyRecordSyncStatus::Pending, $delivery->sync_status);
        $this->assertSame($vehicle->id, $delivery->vehicle_id);
        $this->assertSame($driver->id, $delivery->driver_id);

        $this->assertDatabaseHas('sync_queues', [
            'external_reference' => $delivery->external_reference,
            'entity_type' => 'delivery',
            'direction' => 'push_to_tally',
            'status' => 'pending',
        ]);
    }

    public function test_dispatching_for_an_unmapped_dealer_fails_the_sync_without_enqueueing_but_still_dispatches(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => null]);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->fullyAllocatedApprovedOrder($dealer, $product);

        $this->actingAs($manager)->post(route('orders.dispatch', $order), [
            'delivery_date' => now()->toDateString(),
        ])->assertRedirect();

        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(DeliveryStatus::Dispatched, $delivery->status);
        $this->assertSame(TallyRecordSyncStatus::Failed, $delivery->sync_status);
        $this->assertNotNull($delivery->sync_error);
        $this->assertDatabaseMissing('sync_queues', ['entity_type' => 'delivery', 'entity_id' => $delivery->id]);
    }

    public function test_a_dispatched_delivery_can_be_marked_delivered(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->fullyAllocatedApprovedOrder($dealer, $product);

        $this->actingAs($manager)->post(route('orders.dispatch', $order), ['delivery_date' => now()->toDateString()]);
        $delivery = Delivery::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($manager)->patch(route('deliveries.deliver', $delivery))->assertRedirect();

        $delivery->refresh();
        $this->assertSame(DeliveryStatus::Delivered, $delivery->status);
        $this->assertNotNull($delivery->delivered_at);
    }

    public function test_a_sales_executive_cannot_dispatch_or_mark_delivered(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->fullyAllocatedApprovedOrder($dealer, $product);

        $this->actingAs($executive)->post(route('orders.dispatch', $order), [
            'delivery_date' => now()->toDateString(),
        ])->assertForbidden();

        $delivery = Delivery::factory()->create(['order_id' => $order->id]);
        $this->actingAs($executive)->patch(route('deliveries.deliver', $delivery))->assertForbidden();
    }

    public function test_a_sales_executive_can_view_the_deliveries_list_and_a_single_delivery(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'user_id' => $executive->id]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 100]);
        $delivery = Delivery::factory()->create(['order_id' => $order->id]);

        $this->actingAs($executive)->get(route('deliveries.index'))->assertOk();
        $this->actingAs($executive)->get(route('deliveries.show', $delivery))->assertOk();
    }
}
