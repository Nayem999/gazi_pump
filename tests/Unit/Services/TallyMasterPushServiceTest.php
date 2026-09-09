<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\SyncQueue;
use App\Models\TallyMapping;
use App\Services\TallyMasterPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The SFA -> Tally direction: creating masters inside the customer's
 * accounting system for records that exist only here.
 *
 * This is the only part of the integration that writes permanent masters
 * into Tally, so most of what is asserted below is about restraint — what
 * it declines to push, and why.
 */
class TallyMasterPushServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sfa.tally.master_push_enabled', true);
        config()->set('sfa.tally.dealer_ledger_group', 'Sundry Debtors');
        config()->set('sfa.tally.retailer_ledger_group', null);
    }

    private function service(): TallyMasterPushService
    {
        return app(TallyMasterPushService::class);
    }

    public function test_it_queues_a_create_for_an_active_unmapped_dealer(): void
    {
        $dealer = Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        $result = $this->service()->enqueueAll();

        $this->assertSame(1, $result['queued']);

        $job = SyncQueue::where('entity_type', 'dealer')->where('direction', 'push_to_tally')->firstOrFail();

        $this->assertSame($dealer->id, $job->entity_id);
        $this->assertSame('Savar Pump House', $job->payload['name']);
        $this->assertSame('Sundry Debtors', $job->payload['group']);
    }

    public function test_nothing_is_queued_while_the_push_is_switched_off(): void
    {
        // Creating masters in someone's accounting system is opt-in.
        config()->set('sfa.tally.master_push_enabled', false);
        Dealer::factory()->create(['tally_guid' => null, 'status' => true]);

        $result = $this->service()->enqueueAll();

        $this->assertTrue($result['disabled']);
        $this->assertSame(0, $result['queued']);
        $this->assertSame(0, SyncQueue::count());
    }

    public function test_an_already_mapped_record_is_never_pushed(): void
    {
        Dealer::factory()->create(['tally_guid' => 'GUID-EXISTS', 'status' => true]);

        $this->assertSame(0, $this->service()->enqueueAll()['queued']);
    }

    public function test_an_inactive_record_is_never_pushed(): void
    {
        // An unmapped inactive record is almost always a stub imported FROM
        // Tally that nobody has completed. Pushing it back would create a
        // duplicate of the very master it came from.
        Dealer::factory()->create(['name' => 'Dealer 1', 'tally_guid' => null, 'status' => false]);

        $this->assertSame(0, $this->service()->enqueueAll()['queued']);
    }

    public function test_a_retailer_is_not_pushed_without_a_group_and_the_reason_is_reported(): void
    {
        // The parent dealer is pinned as already mapped so only the
        // retailer is in play — the factory would otherwise create an
        // unmapped active dealer that is itself pushable.
        Retailer::factory()->for(Dealer::factory()->state(['tally_guid' => 'GUID-PARENT']))
            ->create(['name' => 'Shop A', 'tally_guid' => null, 'status' => true]);

        $result = $this->service()->enqueueAll();

        $this->assertSame(0, $result['queued']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('RETAILER_LEDGER_GROUP', $result['skipped'][0]);
    }

    public function test_a_retailer_is_pushed_once_its_group_is_configured(): void
    {
        config()->set('sfa.tally.retailer_ledger_group', 'Retail Customers');
        Retailer::factory()->for(Dealer::factory()->state(['tally_guid' => 'GUID-PARENT']))
            ->create(['name' => 'Shop A', 'tally_guid' => null, 'status' => true]);

        $result = $this->service()->enqueueAll();

        $this->assertSame(1, $result['queued']);

        $job = SyncQueue::where('entity_type', 'retailer')->firstOrFail();

        $this->assertSame('Retail Customers', $job->payload['group']);
    }

    public function test_products_and_depots_carry_the_fields_tally_requires(): void
    {
        config()->set('sfa.tally.stock_item_unit', 'PCS');
        Product::factory()->create(['name' => 'Gazi Tubewell', 'tally_guid' => null, 'status' => true]);
        Depot::factory()->create(['name' => 'Main Depot', 'tally_guid' => null, 'status' => true]);

        $this->service()->enqueueAll();

        $product = SyncQueue::where('entity_type', 'product')->firstOrFail();
        // Tally rejects a stock item with no unit once it holds quantities.
        $this->assertSame('PCS', $product->payload['unit']);

        $depot = SyncQueue::where('entity_type', 'depot')->firstOrFail();
        $this->assertSame('Main Depot', $depot->payload['name']);
    }

    public function test_queueing_twice_does_not_duplicate_the_work(): void
    {
        Dealer::factory()->create(['tally_guid' => null, 'status' => true]);

        $this->service()->enqueueAll();
        $second = $this->service()->enqueueAll();

        $this->assertSame(0, $second['queued']);
        $this->assertSame(1, SyncQueue::where('direction', 'push_to_tally')->count());
    }

    public function test_a_completed_push_is_not_queued_again_once_the_record_is_mapped(): void
    {
        $dealer = Dealer::factory()->create(['tally_guid' => null, 'status' => true]);

        $this->service()->enqueueAll();
        $job = SyncQueue::where('direction', 'push_to_tally')->firstOrFail();

        $this->service()->applyPushResult($job, 'GUID-ASSIGNED');
        SyncQueue::query()->update(['status' => SyncStatus::Success, 'completed_at' => now()]);

        $this->assertSame(0, $this->service()->enqueueAll()['queued']);
        $this->assertSame('GUID-ASSIGNED', $dealer->fresh()->tally_guid);
    }

    public function test_applying_a_push_result_maps_the_record_both_ways(): void
    {
        $dealer = Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);
        $this->service()->enqueueAll();
        $job = SyncQueue::where('direction', 'push_to_tally')->firstOrFail();

        $this->assertTrue($this->service()->applyPushResult($job, 'GUID-ASSIGNED'));

        $this->assertSame('GUID-ASSIGNED', $dealer->fresh()->tally_guid);
        $this->assertDatabaseHas('tally_mappings', [
            'entity_type' => 'dealer',
            'sfa_id' => $dealer->id,
            'tally_guid' => 'GUID-ASSIGNED',
        ]);
    }

    public function test_an_empty_guid_is_refused_rather_than_recorded(): void
    {
        $dealer = Dealer::factory()->create(['tally_guid' => null, 'status' => true]);
        $this->service()->enqueueAll();
        $job = SyncQueue::where('direction', 'push_to_tally')->firstOrFail();

        $this->assertFalse($this->service()->applyPushResult($job, '  '));
        $this->assertNull($dealer->fresh()->tally_guid);
        $this->assertSame(0, TallyMapping::count());
    }

    public function test_pending_lists_what_would_be_created_without_queueing_it(): void
    {
        // The dashboard shows this before the operator commits: writing
        // into an accounting system should never be a surprise.
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);
        Product::factory()->create(['name' => 'Gazi Tubewell', 'tally_guid' => null, 'status' => true]);

        $pending = $this->service()->pending();

        $this->assertSame(['Savar Pump House'], $pending['dealer']->pluck('name')->all());
        $this->assertSame(['Gazi Tubewell'], $pending['product']->pluck('name')->all());
        $this->assertSame(0, SyncQueue::count(), 'listing must not queue anything');
    }
}
