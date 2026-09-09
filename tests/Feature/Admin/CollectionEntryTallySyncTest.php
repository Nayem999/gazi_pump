<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\TallyRecordSyncStatus;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use App\Services\CollectionEntryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionEntryTallySyncTest extends TestCase
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

    public function test_a_new_collection_is_stamped_with_a_stable_external_reference(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $entry = app(CollectionEntryService::class)->create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 100,
            'payment_method' => 'cash',
        ]);

        $this->assertMatchesRegularExpression('/^SFA-COL-\d{8}-\d{6}$/', $entry->external_reference);
        $this->assertSame(TallyRecordSyncStatus::NotSynced, $entry->sync_status);
    }

    public function test_approving_a_fully_mapped_collection_enqueues_a_tally_sync_job(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        $entry = CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'status' => 'pending',
            'external_reference' => 'SFA-COL-20260101-000001',
        ]);

        $this->actingAs($manager)->patch(route('collection-entries.approve', $entry))->assertRedirect();

        $entry->refresh();
        $this->assertSame(TallyRecordSyncStatus::Pending, $entry->sync_status);
        $this->assertDatabaseHas('sync_queues', [
            'external_reference' => 'SFA-COL-20260101-000001',
            'entity_type' => 'collection',
            'direction' => 'push_to_tally',
            'status' => 'pending',
        ]);
    }

    public function test_approving_a_collection_for_an_unmapped_dealer_fails_the_sync_without_enqueueing(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => null]);
        $entry = CollectionEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'status' => 'pending',
            'external_reference' => 'SFA-COL-20260101-000002',
        ]);

        $this->actingAs($manager)->patch(route('collection-entries.approve', $entry))->assertRedirect();

        $entry->refresh();
        $this->assertSame(TallyRecordSyncStatus::Failed, $entry->sync_status);
        $this->assertDatabaseMissing('sync_queues', ['external_reference' => 'SFA-COL-20260101-000002']);
        $this->assertSame('approved', $entry->status->value);
    }
}
