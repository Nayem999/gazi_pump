<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\TallyRecordSyncStatus;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 of the Tally integration: reviving Order as the live transactional
 * backbone means every approved order should now push toward Tally through
 * the Phase 1 sync queue.
 */
class OrderTallySyncTest extends TestCase
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

    private function orderWithItem(Dealer $dealer, Product $product): Order
    {
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'status' => 'pending', 'total_amount' => 500]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        return $order;
    }

    public function test_a_new_order_is_stamped_with_a_stable_external_reference(): void
    {
        $manager = $this->generalManager();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $this->actingAs($manager)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0]],
        ]);

        $order = Order::firstOrFail();
        $this->assertMatchesRegularExpression('/^SFA-SO-\d{8}-\d{6}$/', $order->external_reference);
        $this->assertSame(TallyRecordSyncStatus::NotSynced, $order->sync_status);
    }

    public function test_approving_a_fully_mapped_order_enqueues_a_tally_sync_job(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->orderWithItem($dealer, $product);
        $order->update(['external_reference' => 'SFA-SO-20260101-000001']);

        $this->actingAs($manager)->patch(route('orders.approve', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(TallyRecordSyncStatus::Pending, $order->sync_status);
        $this->assertDatabaseHas('sync_queues', [
            'external_reference' => 'SFA-SO-20260101-000001',
            'entity_type' => 'sales_order',
            'direction' => 'push_to_tally',
            'status' => 'pending',
        ]);
    }

    public function test_approving_an_order_for_an_unmapped_dealer_fails_the_sync_without_enqueueing(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => null]);
        $product = Product::factory()->create(['tally_guid' => 'PRODUCT-GUID-1']);
        $order = $this->orderWithItem($dealer, $product);
        $order->update(['external_reference' => 'SFA-SO-20260101-000002']);

        $this->actingAs($manager)->patch(route('orders.approve', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(TallyRecordSyncStatus::Failed, $order->sync_status);
        $this->assertNotNull($order->sync_error);
        $this->assertDatabaseMissing('sync_queues', ['external_reference' => 'SFA-SO-20260101-000002']);
        // The order itself is still approved — a missing Tally mapping is a
        // sync-side problem, never a reason to block the business decision.
        $this->assertSame('approved', $order->status->value);
    }

    public function test_approving_an_order_for_an_unmapped_product_fails_the_sync_without_enqueueing(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        $product = Product::factory()->create(['tally_guid' => null]);
        $order = $this->orderWithItem($dealer, $product);
        $order->update(['external_reference' => 'SFA-SO-20260101-000003']);

        $this->actingAs($manager)->patch(route('orders.approve', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(TallyRecordSyncStatus::Failed, $order->sync_status);
        $this->assertDatabaseMissing('sync_queues', ['external_reference' => 'SFA-SO-20260101-000003']);
    }
}
