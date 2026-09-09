<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SyncStatus;
use App\Models\Dealer;
use App\Models\SyncQueue;
use App\Models\TallyConnection;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The dashboard's "Sync Now" button: one action that queues a full
 * Tally -> SFA refresh. It queues rather than performs, because only the
 * Sync Agent can reach Tally.
 */
class TallyManualSyncTest extends TestCase
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

    public function test_it_queues_every_master_list_and_each_mapped_dealers_ledger(): void
    {
        $manager = $this->generalManager();
        Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);
        Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-2']);
        // Unmapped: there is nothing to pull for it, so it must be skipped.
        Dealer::factory()->create(['tally_guid' => null]);

        $this->actingAs($manager)->post(route('tally-integration.sync-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach (['dealer', 'retailer', 'product', 'depot'] as $entityType) {
            $this->assertDatabaseHas('sync_queues', [
                'entity_type' => $entityType,
                'direction' => 'pull_from_tally',
                'status' => 'pending',
            ]);
        }

        $this->assertSame(2, SyncQueue::where('entity_type', 'ledger')->count(), 'only mapped dealers get a ledger pull');
    }

    public function test_clicking_twice_does_not_queue_the_work_again(): void
    {
        $manager = $this->generalManager();
        Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);

        $this->actingAs($manager)->post(route('tally-integration.sync-all'));
        $afterFirst = SyncQueue::count();

        $response = $this->actingAs($manager)->post(route('tally-integration.sync-all'));

        $this->assertSame($afterFirst, SyncQueue::count(), 'a second click must not duplicate pending work');
        $response->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'Nothing new to queue'));
    }

    public function test_it_queues_again_once_the_previous_run_has_completed(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->post(route('tally-integration.sync-all'));
        SyncQueue::query()->update(['status' => SyncStatus::Success, 'completed_at' => now()]);

        // A second later, as any real re-sync would be. The pull's
        // external_reference is stamped to the second, so it doubles as a
        // last-ditch idempotency guard for two clicks inside one second —
        // the pending-status check is what actually stops repeat clicks.
        $this->travel(1)->second();

        $this->actingAs($manager)->post(route('tally-integration.sync-all'));

        // Four masters queued twice over, the first batch now completed.
        $this->assertSame(8, SyncQueue::where('direction', 'pull_from_tally')->count());
    }

    public function test_two_clicks_inside_one_second_cannot_double_queue_even_if_the_first_completed(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->post(route('tally-integration.sync-all'));
        SyncQueue::query()->update(['status' => SyncStatus::Success, 'completed_at' => now()]);

        // No time travel: the identical external_reference makes enqueue()
        // idempotent, so even a completed batch isn't duplicated.
        $this->actingAs($manager)->post(route('tally-integration.sync-all'));

        $this->assertSame(4, SyncQueue::where('direction', 'pull_from_tally')->count());
    }

    public function test_it_says_so_when_no_dealer_is_mapped_yet(): void
    {
        $manager = $this->generalManager();
        Dealer::factory()->create(['tally_guid' => null]);

        $this->actingAs($manager)->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'none have a Tally mapping'));

        $this->assertSame(0, SyncQueue::where('entity_type', 'ledger')->count());
    }

    public function test_the_button_and_action_are_closed_to_a_sales_executive(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->post(route('tally-integration.sync-all'))->assertForbidden();
        $this->assertSame(0, SyncQueue::count());
    }

    public function test_the_queue_screen_reports_what_a_master_pull_changed(): void
    {
        // "Success" alone is ambiguous for a master pull — it can mean
        // everything mapped, or that Tally answered with rows nothing could
        // be done with. The outcome counts are what tell them apart.
        $item = SyncQueue::factory()->create([
            'entity_type' => 'dealer',
            'direction' => 'pull_from_tally',
            'status' => SyncStatus::Success,
            'response' => ['applied' => [
                'linked' => 2,
                'already' => 1,
                'imported' => ['Dealer 1'],
                'conflicts' => ['Gazi Appliance Corner'],
                'unmatched' => [],
            ]],
        ]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.sync-queue'))
            ->assertOk()
            ->assertSee('2 linked')
            ->assertSee('1 imported')
            ->assertSee('1 conflicts')
            ->assertSee('1 already mapped')
            // The names behind the counts, so a conflict is actionable.
            ->assertSee('Gazi Appliance Corner', false);

        $this->assertStringNotContainsString('unmatched', implode(' ', array_column($item->outcomeChips(), 'label')));
    }

    public function test_a_pull_that_mapped_nothing_says_so_rather_than_showing_blank(): void
    {
        SyncQueue::factory()->create([
            'entity_type' => 'retailer',
            'direction' => 'pull_from_tally',
            'status' => SyncStatus::Success,
            'response' => ['applied' => [
                'linked' => 0, 'already' => 0, 'imported' => [], 'conflicts' => [], 'unmatched' => [],
            ]],
        ]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.sync-queue'))
            ->assertOk()
            ->assertSee('nothing to map');
    }

    public function test_a_deliberately_skipped_pull_explains_itself(): void
    {
        // The retailer pull reports this when no retailer ledger group is
        // configured — a success with no rows, which would otherwise look
        // identical to a pull that simply found nothing.
        SyncQueue::factory()->create([
            'entity_type' => 'retailer',
            'direction' => 'pull_from_tally',
            'status' => SyncStatus::Success,
            // Carries an all-zero `applied` too, because the skipped pull
            // still goes through the master sync with no rows — the note
            // must win over the counts, or the row reads "nothing to map"
            // and hides the missing setting.
            'response' => [
                'rows' => [],
                'skipped' => 'TALLY_RETAILER_LEDGER_GROUP is not set.',
                'applied' => ['linked' => 0, 'already' => 0, 'imported' => [], 'conflicts' => [], 'unmatched' => [], 'deleted_here' => []],
            ],
        ]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.sync-queue'))
            ->assertOk()
            ->assertSee('Skipped')
            ->assertSee('TALLY_RETAILER_LEDGER_GROUP is not set.', false)
            ->assertDontSee('nothing to map');
    }

    public function test_sync_now_also_queues_sfa_only_records_into_tally(): void
    {
        config()->set('sfa.tally.master_push_enabled', true);
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'to create in Tally'));

        $this->assertDatabaseHas('sync_queues', [
            'entity_type' => 'dealer',
            'direction' => 'push_to_tally',
            'status' => 'pending',
        ]);
    }

    public function test_sync_now_writes_nothing_into_tally_while_the_push_is_off(): void
    {
        // The default. A sync that only reads must stay a sync that only
        // reads — creating masters in an accounting system is opt-in.
        config()->set('sfa.tally.master_push_enabled', false);
        Dealer::factory()->create(['tally_guid' => null, 'status' => true]);

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))->assertRedirect();

        $this->assertSame(0, SyncQueue::where('direction', 'push_to_tally')->count());
    }

    public function test_the_dashboard_names_what_would_be_created_in_tally(): void
    {
        config()->set('sfa.tally.master_push_enabled', true);
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertSee('Will create 1 record(s) in Tally')
            ->assertSee('Savar Pump House');
    }

    public function test_the_dashboard_says_when_creating_records_in_tally_is_off(): void
    {
        config()->set('sfa.tally.master_push_enabled', false);
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertSee('Creating records in Tally is off')
            ->assertDontSee('Will create');
    }

    public function test_the_queue_screen_reports_a_master_created_in_tally(): void
    {
        // The only outcome in this system that CHANGED the accounting data
        // rather than reading it, so it gets its own wording.
        SyncQueue::factory()->create([
            'entity_type' => 'dealer',
            'direction' => 'push_to_tally',
            'status' => SyncStatus::Success,
            'response' => ['mapped' => true],
        ]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.sync-queue'))
            ->assertOk()
            ->assertSee('created in Tally');
    }

    public function test_a_push_that_could_not_be_mapped_back_is_called_out(): void
    {
        // The dangerous half-state: the master IS in Tally but SFA cannot
        // recognise it, so a retry would collide on the name.
        SyncQueue::factory()->create([
            'entity_type' => 'dealer',
            'direction' => 'push_to_tally',
            'status' => SyncStatus::Success,
            'response' => ['mapped' => false],
        ]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.sync-queue'))
            ->assertOk()
            ->assertSee('created in Tally, not mapped here');
    }

    public function test_a_stalled_queue_reports_the_dead_agent_not_just_already_queued(): void
    {
        // The bug this pins: with work queued and no agent collecting it,
        // Sync Now used to answer "already waiting for the agent" — the
        // same words a healthy queue uses — so a stalled sync looked like a
        // working one indefinitely.
        TallyConnection::factory()->create(['is_active' => true, 'last_heartbeat_at' => now()->subDay()]);
        Dealer::factory()->create(['tally_guid' => 'GUID-1']);

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'));

        // Second click: nothing new to queue, and the agent is dead.
        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', function (string $msg) {
                return str_contains($msg, 'job(s) are already waiting')
                    && str_contains($msg, 'last checked in')
                    && str_contains($msg, 'will not move');
            });
    }

    public function test_a_healthy_idle_queue_still_says_simply_already_queued(): void
    {
        TallyConnection::factory()->create(['is_active' => true, 'last_heartbeat_at' => now()]);
        Dealer::factory()->create(['tally_guid' => 'GUID-1']);

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'));

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', function (string $msg) {
                return str_contains($msg, 'already waiting')
                    && str_contains($msg, 'online')
                    && ! str_contains($msg, 'will not move');
            });
    }

    public function test_queueing_new_work_warns_when_no_agent_can_collect_it(): void
    {
        TallyConnection::factory()->create(['is_active' => true, 'last_heartbeat_at' => now()->subDay()]);

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'Queued')
                && str_contains($msg, 'will not move'));
    }

    public function test_it_says_so_when_no_connection_exists_at_all(): void
    {
        $this->assertSame(0, TallyConnection::count());

        $this->actingAs($this->generalManager())->post(route('tally-integration.sync-all'))
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'No active Tally connection is configured'));
    }

    public function test_the_dashboard_warns_about_a_stalled_queue(): void
    {
        TallyConnection::factory()->create(['is_active' => true, 'last_heartbeat_at' => now()->subDay()]);
        SyncQueue::factory()->create(['status' => SyncStatus::Pending]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertSee('queued but nothing is collecting them')
            ->assertSee('clicking Sync Now again will not help');
    }

    public function test_the_dashboard_does_not_warn_while_the_agent_is_alive(): void
    {
        TallyConnection::factory()->create(['is_active' => true, 'last_heartbeat_at' => now()]);
        SyncQueue::factory()->create(['status' => SyncStatus::Pending]);

        $this->actingAs($this->generalManager())->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertDontSee('queued but nothing is collecting them');
    }

    public function test_the_dashboard_shows_the_button_to_a_manager_only(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($this->generalManager())->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertSee('Sync Now');

        // A Sales Executive can't even reach the dashboard.
        $this->actingAs($executive)->get(route('tally-integration.dashboard'))->assertForbidden();
    }
}
