<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Models\SyncQueue;
use App\Models\TallyMapping;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallyIntegrationDashboardTest extends TestCase
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

    public function test_dashboard_shows_todays_success_count_and_current_queue_totals(): void
    {
        $manager = $this->generalManager();
        SyncQueue::factory()->create(['status' => SyncStatus::Success, 'completed_at' => now()]);
        SyncQueue::factory()->create(['status' => SyncStatus::Pending]);
        SyncQueue::factory()->create(['status' => SyncStatus::Failed]);
        SyncQueue::factory()->create(['status' => SyncStatus::Retry]);

        $this->actingAs($manager)->get(route('tally-integration.dashboard'))
            ->assertOk()
            ->assertSee('Successful Today')
            ->assertSee('Pending')
            ->assertSee('Failed')
            ->assertSee('Retrying');
    }

    public function test_sync_queue_page_lists_jobs_and_can_be_filtered_by_status(): void
    {
        $manager = $this->generalManager();
        SyncQueue::factory()->create(['external_reference' => 'SFA-KEEP', 'status' => SyncStatus::Failed]);
        SyncQueue::factory()->create(['external_reference' => 'SFA-HIDE', 'status' => SyncStatus::Success]);

        $response = $this->actingAs($manager)->get(route('tally-integration.sync-queue', ['status' => 'failed']));

        $response->assertOk()->assertSee('SFA-KEEP')->assertDontSee('SFA-HIDE');
    }

    public function test_retrying_a_failed_job_resets_it_to_pending(): void
    {
        $manager = $this->generalManager();
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Failed, 'attempt_count' => 5, 'error_message' => 'boom']);

        $this->actingAs($manager)->post(route('tally-integration.sync-queue.retry', $item))->assertRedirect();

        $item->refresh();
        $this->assertSame(SyncStatus::Pending, $item->status);
        $this->assertSame(0, $item->attempt_count);
    }

    public function test_a_sales_executive_cannot_retry_a_job(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Failed]);

        $this->actingAs($executive)->post(route('tally-integration.sync-queue.retry', $item))->assertForbidden();
    }

    public function test_sync_logs_page_can_be_filtered_by_entity_type(): void
    {
        $manager = $this->generalManager();
        \App\Models\SyncLog::factory()->create(['entity_type' => TallyEntityType::Dealer, 'external_reference' => 'SFA-DEALER-LOG']);
        \App\Models\SyncLog::factory()->create(['entity_type' => TallyEntityType::Product, 'external_reference' => 'SFA-PRODUCT-LOG']);

        $response = $this->actingAs($manager)->get(route('tally-integration.sync-logs', ['entity_type' => 'dealer']));

        $response->assertOk()->assertSee('SFA-DEALER-LOG')->assertDontSee('SFA-PRODUCT-LOG');
    }

    public function test_mapping_page_lists_mappings(): void
    {
        $manager = $this->generalManager();
        TallyMapping::factory()->create(['tally_name' => 'Rahman Enterprise']);

        $this->actingAs($manager)->get(route('tally-integration.mapping'))
            ->assertOk()
            ->assertSee('Rahman Enterprise');
    }

    public function test_reconciliation_page_flags_an_unmapped_dealer_as_not_synced(): void
    {
        $manager = $this->generalManager();
        \App\Models\Dealer::factory()->create(['name' => 'Unmapped Dealer Co']);

        $this->actingAs($manager)->get(route('tally-integration.reconciliation'))
            ->assertOk()
            ->assertSee('Unmapped Dealer Co')
            ->assertSee('Not Synced');
    }

    public function test_reconciliation_page_flags_a_dealer_whose_sfa_and_tally_balances_disagree(): void
    {
        $manager = $this->generalManager();
        $dealer = \App\Models\Dealer::factory()->create(['name' => 'Mismatched Traders']);
        \App\Models\Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        \App\Models\LedgerEntry::factory()->create(['dealer_id' => $dealer->id, 'debit_amount' => 400, 'credit_amount' => 0]);

        // SFA thinks the dealer owes 1000 (its own Order-only estimate);
        // Tally's real ledger only shows a 400 debit — a real mismatch.
        $this->actingAs($manager)->get(route('tally-integration.reconciliation'))
            ->assertOk()
            ->assertSee('Mismatched Traders')
            ->assertSee('1,000.00')
            ->assertSee('400.00');
    }

    public function test_reconciliation_page_lists_a_failed_order_sync(): void
    {
        $manager = $this->generalManager();
        $order = \App\Models\Order::factory()->create([
            'sync_status' => 'failed',
            'sync_error' => 'Dealer has no Tally mapping yet.',
            'external_reference' => 'SFA-SO-20260101-000001',
        ]);

        $this->actingAs($manager)->get(route('tally-integration.reconciliation'))
            ->assertOk()
            ->assertSee($order->external_reference)
            ->assertSee('Dealer has no Tally mapping yet.');
    }

    public function test_a_sales_executive_cannot_view_reconciliation(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('tally-integration.reconciliation'))->assertForbidden();
    }
}
