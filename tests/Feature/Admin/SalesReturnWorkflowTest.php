<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SalesReturnStatus;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 6: full request -> approve -> dispatch -> depot receiving workflow,
 * and its push to Tally as a Credit Note once received.
 */
class SalesReturnWorkflowTest extends TestCase
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

    private function salesExecutive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function approvedOrderWithItem(Dealer $dealer, Product $product, User $executive, int $quantity = 10): Order
    {
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'user_id' => $executive->id, 'status' => 'approved', 'total_amount' => $quantity * 100]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => $quantity * 100]);

        return $order;
    }

    public function test_a_sales_executive_can_request_a_return_against_their_own_approved_order(): void
    {
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 10);
        $item = $order->items->first();

        $response = $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'reason' => 'Damaged in transit',
            'items' => [['order_item_id' => $item->id, 'quantity' => 3]],
        ]);

        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();
        $response->assertRedirect(route('sales-returns.show', $return));

        $this->assertSame(SalesReturnStatus::Requested, $return->status);
        $this->assertMatchesRegularExpression('/^SFA-SR-\d{8}-\d{6}$/', $return->external_reference);
        $this->assertSame(1, $return->items()->count());
        $this->assertSame(3.0, (float) $return->items->first()->requested_qty);
    }

    public function test_a_return_cannot_be_requested_against_a_pending_order(): void
    {
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'user_id' => $executive->id, 'status' => 'pending']);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('order');

        $this->assertSame(0, SalesReturn::count());
    }

    public function test_requesting_more_than_what_is_left_to_return_is_rejected(): void
    {
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 5);
        $item = $order->items->first();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, SalesReturn::count());
    }

    public function test_a_second_request_is_capped_by_what_the_first_already_claimed(): void
    {
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 10);
        $item = $order->items->first();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 7]],
        ])->assertRedirect();

        // Only 3 left now (10 - 7 already requested).
        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 4]],
        ])->assertSessionHasErrors('items');
    }

    public function test_the_full_workflow_from_request_to_a_tally_credit_note_sync(): void
    {
        $manager = $this->generalManager();
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 10);
        $item = $order->items->first();
        $vehicle = Vehicle::factory()->create();
        $driver = Driver::factory()->create();
        $depot = Depot::factory()->create();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'reason' => 'Wrong item shipped',
            'items' => [['order_item_id' => $item->id, 'quantity' => 4]],
        ]);
        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($manager)->patch(route('sales-returns.approve', $return))->assertRedirect();
        $this->assertSame(SalesReturnStatus::Approved, $return->fresh()->status);

        $this->actingAs($manager)->post(route('sales-returns.dispatch', $return), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
        ])->assertRedirect();
        $return->refresh();
        $this->assertSame(SalesReturnStatus::Dispatched, $return->status);
        $this->assertSame($vehicle->id, $return->vehicle_id);

        $returnItem = $return->items->first();
        $this->actingAs($manager)->post(route('sales-returns.receive', $return), [
            'receiving_depot_id' => $depot->id,
            'items' => [['item_id' => $returnItem->id, 'received_qty' => 3]],
        ])->assertRedirect();

        $return->refresh();
        $this->assertSame(SalesReturnStatus::Received, $return->status);
        $this->assertSame($depot->id, $return->receiving_depot_id);
        $this->assertSame(3.0, (float) $return->items->first()->received_qty);

        $this->assertSame(TallyRecordSyncStatus::Pending, $return->sync_status);
        $this->assertDatabaseHas('sync_queues', [
            'external_reference' => $return->external_reference,
            'entity_type' => 'return',
            'direction' => 'push_to_tally',
            'status' => 'pending',
        ]);

        // Credit note amount is based on received_qty (3), not the
        // originally requested_qty (4).
        $job = \App\Models\SyncQueue::where('external_reference', $return->external_reference)->firstOrFail();
        $this->assertSame(300.0, (float) $job->payload['total_amount']);
    }

    public function test_receiving_for_an_unmapped_dealer_fails_the_sync_without_enqueueing(): void
    {
        $manager = $this->generalManager();
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create(['tally_guid' => null]);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 5);
        $item = $order->items->first();
        $depot = Depot::factory()->create();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 2]],
        ]);
        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($manager)->patch(route('sales-returns.approve', $return));
        $this->actingAs($manager)->post(route('sales-returns.dispatch', $return), []);

        $returnItem = $return->items->first();
        $this->actingAs($manager)->post(route('sales-returns.receive', $return), [
            'receiving_depot_id' => $depot->id,
            'items' => [['item_id' => $returnItem->id, 'received_qty' => 2]],
        ]);

        $return->refresh();
        $this->assertSame(SalesReturnStatus::Received, $return->status);
        $this->assertSame(TallyRecordSyncStatus::Failed, $return->sync_status);
        $this->assertNotNull($return->sync_error);
        $this->assertDatabaseMissing('sync_queues', ['external_reference' => $return->external_reference]);
    }

    public function test_a_return_cannot_be_dispatched_before_it_is_approved(): void
    {
        $manager = $this->generalManager();
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 5);
        $item = $order->items->first();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 2]],
        ]);
        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($manager)->post(route('sales-returns.dispatch', $return), [])
            ->assertSessionHasErrors('status');

        $this->assertSame(SalesReturnStatus::Requested, $return->fresh()->status);
    }

    public function test_a_sales_executive_cannot_approve_dispatch_or_receive_a_return(): void
    {
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 5);
        $item = $order->items->first();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 2]],
        ]);
        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($executive)->patch(route('sales-returns.approve', $return))->assertForbidden();
        $this->actingAs($executive)->post(route('sales-returns.dispatch', $return), [])->assertForbidden();
    }

    public function test_a_general_manager_can_reject_a_requested_return(): void
    {
        $manager = $this->generalManager();
        $executive = $this->salesExecutive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = $this->approvedOrderWithItem($dealer, $product, $executive, 5);
        $item = $order->items->first();

        $this->actingAs($executive)->post(route('orders.returns.store', $order), [
            'items' => [['order_item_id' => $item->id, 'quantity' => 2]],
        ]);
        $return = SalesReturn::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($manager)->patch(route('sales-returns.reject', $return))->assertRedirect();
        $this->assertSame(SalesReturnStatus::Rejected, $return->fresh()->status);
    }
}
