<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SyncQueue;
use App\Models\TallyConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallyIntegrationAgentTest extends TestCase
{
    use RefreshDatabase;

    private function connectionWithToken(string $plainToken, array $overrides = []): TallyConnection
    {
        return TallyConnection::factory()->create([
            'sync_agent_token' => hash('sha256', $plainToken),
            ...$overrides,
        ]);
    }

    public function test_heartbeat_requires_a_bearer_token(): void
    {
        $this->postJson('/api/v1/integration/tally/agent/heartbeat', [])->assertStatus(401);
    }

    public function test_a_human_sanctum_token_cannot_authenticate_as_the_sync_agent(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('phone')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/integration/tally/agent/heartbeat', [])
            ->assertStatus(401);
    }

    public function test_an_inactive_connections_token_is_rejected(): void
    {
        $connection = $this->connectionWithToken('plain-token-123', ['is_active' => false]);

        $this->withHeader('Authorization', 'Bearer plain-token-123')
            ->postJson('/api/v1/integration/tally/agent/heartbeat', [])
            ->assertStatus(401);

        $this->assertNull($connection->fresh()->last_heartbeat_at);
    }

    public function test_heartbeat_records_the_current_time_and_optional_company_guid(): void
    {
        $connection = $this->connectionWithToken('plain-token-abc', ['tally_company_guid' => null]);

        $this->withHeader('Authorization', 'Bearer plain-token-abc')
            ->postJson('/api/v1/integration/tally/agent/heartbeat', ['tally_company_guid' => 'GUID-DISCOVERED'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $connection->refresh();
        $this->assertNotNull($connection->last_heartbeat_at);
        $this->assertSame('GUID-DISCOVERED', $connection->tally_company_guid);
    }

    public function test_jobs_endpoint_claims_pending_rows_and_marks_them_processing(): void
    {
        $this->connectionWithToken('plain-token-jobs');
        SyncQueue::factory()->count(3)->create(['status' => SyncStatus::Pending]);

        $response = $this->withHeader('Authorization', 'Bearer plain-token-jobs')
            ->getJson('/api/v1/integration/tally/agent/jobs?limit=2');

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertSame(2, SyncQueue::where('status', SyncStatus::Processing)->count());
    }

    public function test_job_result_success_marks_the_row_synced_and_stores_the_tally_reference(): void
    {
        $this->connectionWithToken('plain-token-result');
        $item = SyncQueue::factory()->create([
            'status' => SyncStatus::Processing,
            'entity_type' => TallyEntityType::Dealer,
            'direction' => SyncDirection::PullFromTally,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer plain-token-result')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$item->id}/result", [
                'success' => true,
                'tally_guid' => 'GUID-1',
                'tally_voucher_number' => 'SV-1',
            ]);

        $response->assertOk()->assertJsonPath('data.status', 'success');
        $this->assertDatabaseHas('sync_logs', ['external_reference' => $item->external_reference, 'tally_guid' => 'GUID-1']);
    }

    public function test_job_result_failure_requires_an_error_code_and_message(): void
    {
        $this->connectionWithToken('plain-token-fail');
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing]);

        $this->withHeader('Authorization', 'Bearer plain-token-fail')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$item->id}/result", ['success' => false])
            ->assertStatus(422);
    }

    public function test_job_result_failure_with_a_retryable_code_schedules_a_retry(): void
    {
        $this->connectionWithToken('plain-token-retry');
        $item = SyncQueue::factory()->create(['status' => SyncStatus::Processing, 'attempt_count' => 1]);

        $this->withHeader('Authorization', 'Bearer plain-token-retry')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$item->id}/result", [
                'success' => false,
                'error_code' => 'TALLY_OFFLINE',
                'error_message' => 'Connection refused',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'retry');
    }

    public function test_stock_sync_requires_a_bearer_token(): void
    {
        $this->postJson('/api/v1/integration/tally/stock-sync', ['rows' => []])->assertStatus(401);
    }

    public function test_stock_sync_applies_a_row_matched_by_tally_guid_and_updates_the_connections_last_sync(): void
    {
        $connection = $this->connectionWithToken('plain-token-stock', ['last_successful_sync_at' => null]);
        $depot = Depot::factory()->create(['tally_guid' => 'GODOWN-GUID-1']);
        $product = Product::factory()->create(['tally_guid' => 'ITEM-GUID-1']);

        $response = $this->withHeader('Authorization', 'Bearer plain-token-stock')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => $product->name,
                    'godown_name' => $depot->name,
                    'stock_item_tally_guid' => 'ITEM-GUID-1',
                    'godown_tally_guid' => 'GODOWN-GUID-1',
                    'opening_qty' => 10,
                    'in_qty' => 5,
                    'out_qty' => 2,
                    'closing_qty' => 13,
                ]],
            ]);

        $response->assertOk()->assertJsonPath('data.applied', 1)->assertJsonPath('data.skipped', 0);

        $this->assertDatabaseHas('product_stocks', [
            'depot_id' => $depot->id,
            'product_id' => $product->id,
            'closing_qty' => 13,
            'available_qty' => 13,
        ]);
        $this->assertNotNull($connection->fresh()->last_successful_sync_at);
    }

    public function test_stock_sync_skips_a_row_whose_depot_or_product_guid_is_unmapped(): void
    {
        $this->connectionWithToken('plain-token-stock-skip');

        $response = $this->withHeader('Authorization', 'Bearer plain-token-stock-skip')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => 'Unknown Item',
                    'godown_name' => 'Unknown Godown',
                    'stock_item_tally_guid' => null,
                    'godown_tally_guid' => null,
                    'opening_qty' => 1,
                    'in_qty' => 0,
                    'out_qty' => 0,
                    'closing_qty' => 1,
                ]],
            ]);

        $response->assertOk()->assertJsonPath('data.applied', 0)->assertJsonPath('data.skipped', 1);
        $this->assertSame(0, ProductStock::count());
    }

    public function test_stock_sync_updates_an_existing_row_without_touching_reserved_or_allocated_qty(): void
    {
        $this->connectionWithToken('plain-token-stock-update');
        $depot = Depot::factory()->create(['tally_guid' => 'GODOWN-GUID-2']);
        $product = Product::factory()->create(['tally_guid' => 'ITEM-GUID-2']);
        ProductStock::factory()->create([
            'depot_id' => $depot->id,
            'product_id' => $product->id,
            'closing_qty' => 5,
            'available_qty' => 5,
            'reserved_qty' => 3,
            'allocated_qty' => 3,
        ]);

        $this->withHeader('Authorization', 'Bearer plain-token-stock-update')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => $product->name,
                    'godown_name' => $depot->name,
                    'stock_item_tally_guid' => 'ITEM-GUID-2',
                    'godown_tally_guid' => 'GODOWN-GUID-2',
                    'opening_qty' => 5,
                    'in_qty' => 10,
                    'out_qty' => 0,
                    'closing_qty' => 15,
                ]],
            ])->assertOk();

        $stock = ProductStock::where('depot_id', $depot->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(15.0, (float) $stock->closing_qty);
        $this->assertSame(15.0, (float) $stock->available_qty);
        // SFA-owned columns are untouched by a Tally-side stock sync.
        $this->assertSame(3.0, (float) $stock->reserved_qty);
        $this->assertSame(3.0, (float) $stock->allocated_qty);
    }

    /**
     * Single-depot mode: the live customer's Tally cannot serve
     * item x godown quantities, so the agent pushes godown-less rows and
     * SFA attributes them to the configured default depot. Row shape is
     * exactly what buildSingleDepotSnapshot() produced against the real
     * instance (Pump Model 1, 45 PCS closing).
     */
    public function test_stock_sync_attributes_a_godownless_row_to_the_configured_default_depot(): void
    {
        $this->connectionWithToken('plain-token-single-depot');
        $depot = Depot::factory()->create(['code' => 'DEP-MAIN']);
        $product = Product::factory()->create(['tally_guid' => 'ITEM-GUID-1']);
        config(['sfa.tally.default_depot_code' => 'DEP-MAIN']);

        $response = $this->withHeader('Authorization', 'Bearer plain-token-single-depot')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => 'Pump Model 1',
                    'godown_name' => null,
                    'stock_item_tally_guid' => 'ITEM-GUID-1',
                    'godown_tally_guid' => null,
                    'opening_qty' => 0,
                    'in_qty' => 0,
                    'out_qty' => 0,
                    'closing_qty' => 45,
                ]],
            ]);

        $response->assertOk()->assertJsonPath('data.applied', 1)->assertJsonPath('data.skipped', 0);

        $this->assertDatabaseHas('product_stocks', [
            'depot_id' => $depot->id,
            'product_id' => $product->id,
            'closing_qty' => 45,
            'available_qty' => 45,
        ]);
    }

    public function test_a_godownless_row_is_skipped_when_no_default_depot_is_configured(): void
    {
        $this->connectionWithToken('plain-token-no-default');
        Product::factory()->create(['tally_guid' => 'ITEM-GUID-1']);
        Depot::factory()->create(['code' => 'DEP-MAIN']);
        config(['sfa.tally.default_depot_code' => null]);

        $this->withHeader('Authorization', 'Bearer plain-token-no-default')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => 'Pump Model 1',
                    'godown_name' => null,
                    'stock_item_tally_guid' => 'ITEM-GUID-1',
                    'godown_tally_guid' => null,
                    'opening_qty' => 0,
                    'in_qty' => 0,
                    'out_qty' => 0,
                    'closing_qty' => 45,
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.applied', 0)
            ->assertJsonPath('data.skipped', 1);

        // Better to record nothing than to land stock on an arbitrary depot.
        $this->assertSame(0, ProductStock::count());
    }

    public function test_single_depot_mode_leaves_movement_columns_alone(): void
    {
        $this->connectionWithToken('plain-token-movement');
        $depot = Depot::factory()->create(['code' => 'DEP-MAIN']);
        $product = Product::factory()->create(['tally_guid' => 'ITEM-GUID-1']);
        config(['sfa.tally.default_depot_code' => 'DEP-MAIN']);

        // Pretend a real per-godown sync had previously stored movement.
        ProductStock::factory()->create([
            'depot_id' => $depot->id,
            'product_id' => $product->id,
            'opening_qty' => 10,
            'in_qty' => 7,
            'out_qty' => 3,
            'closing_qty' => 14,
            'available_qty' => 14,
            'reserved_qty' => 2,
        ]);

        $this->withHeader('Authorization', 'Bearer plain-token-movement')
            ->postJson('/api/v1/integration/tally/stock-sync', [
                'rows' => [[
                    'stock_item_name' => 'Pump Model 1',
                    'godown_name' => null,
                    'stock_item_tally_guid' => 'ITEM-GUID-1',
                    'godown_tally_guid' => null,
                    'closing_qty' => 45,
                ]],
            ])->assertOk();

        $stock = ProductStock::firstOrFail();
        $this->assertSame(45.0, (float) $stock->closing_qty);
        $this->assertSame(45.0, (float) $stock->available_qty);
        // Single-depot mode reports no movement, so "unknown" must not be
        // written as 0 over figures that were real.
        $this->assertSame(10.0, (float) $stock->opening_qty);
        $this->assertSame(7.0, (float) $stock->in_qty);
        $this->assertSame(3.0, (float) $stock->out_qty);
        // And SFA's own overlay is still untouched.
        $this->assertSame(2.0, (float) $stock->reserved_qty);
    }

    public function test_a_successful_ledger_pull_job_result_materializes_ledger_entries(): void
    {
        $this->connectionWithToken('plain-token-ledger');
        $dealer = Dealer::factory()->create();
        $job = SyncQueue::factory()->create([
            'status' => SyncStatus::Processing,
            'entity_type' => TallyEntityType::Ledger,
            'entity_id' => $dealer->id,
            'direction' => SyncDirection::PullFromTally,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer plain-token-ledger')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$job->id}/result", [
                'success' => true,
                'response' => [
                    'rows' => [
                        ['tally_guid' => 'V-1', 'voucher_date' => '2026-08-01', 'voucher_type' => 'Sales', 'voucher_number' => 'SV-1', 'debit_amount' => 1000, 'credit_amount' => 0, 'narration' => null],
                        ['tally_guid' => 'V-2', 'voucher_date' => '2026-08-05', 'voucher_type' => 'Receipt', 'voucher_number' => 'RC-1', 'debit_amount' => 0, 'credit_amount' => 400, 'narration' => null],
                    ],
                ],
            ]);

        $response->assertOk()->assertJsonPath('data.status', 'success');
        $this->assertSame(2, LedgerEntry::where('dealer_id', $dealer->id)->count());
        $this->assertDatabaseHas('ledger_entries', ['tally_guid' => 'V-1', 'debit_amount' => 1000]);
        $this->assertDatabaseHas('ledger_entries', ['tally_guid' => 'V-2', 'credit_amount' => 400]);
    }

    public function test_a_failed_ledger_pull_job_result_does_not_materialize_anything(): void
    {
        $this->connectionWithToken('plain-token-ledger-fail');
        $dealer = Dealer::factory()->create();
        $job = SyncQueue::factory()->create([
            'status' => SyncStatus::Processing,
            'entity_type' => TallyEntityType::Ledger,
            'entity_id' => $dealer->id,
        ]);

        $this->withHeader('Authorization', 'Bearer plain-token-ledger-fail')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$job->id}/result", [
                'success' => false,
                'error_code' => 'TALLY_OFFLINE',
                'error_message' => 'Connection refused',
            ])->assertOk();

        $this->assertSame(0, LedgerEntry::count());
    }

    public function test_a_reported_master_push_result_maps_the_record_here(): void
    {
        // The round trip that matters: the agent created the Ledger in
        // Tally and reports back the GUID Tally assigned it. Without this
        // step the record stays unmapped and the next push would try to
        // create it again, colliding on the name.
        config()->set('sfa.tally.master_push_enabled', true);
        $this->connectionWithToken('agent-token-push');
        $dealer = Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        app(\App\Services\TallyMasterPushService::class)->enqueueAll();
        $job = SyncQueue::where('direction', SyncDirection::PushToTally)->firstOrFail();

        $this->withHeader('Authorization', 'Bearer agent-token-push')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$job->id}/result", [
                'success' => true,
                'tally_guid' => 'GUID-FROM-TALLY',
                'response' => ['RESPONSE' => ['CREATED' => 1]],
            ])
            ->assertOk();

        $this->assertSame('GUID-FROM-TALLY', $dealer->fresh()->tally_guid);
        $this->assertDatabaseHas('tally_mappings', [
            'entity_type' => 'dealer',
            'sfa_id' => $dealer->id,
            'tally_guid' => 'GUID-FROM-TALLY',
        ]);
    }

    public function test_a_failed_master_push_leaves_the_record_unmapped(): void
    {
        config()->set('sfa.tally.master_push_enabled', true);
        $this->connectionWithToken('agent-token-fail');
        $dealer = Dealer::factory()->create(['tally_guid' => null, 'status' => true]);

        app(\App\Services\TallyMasterPushService::class)->enqueueAll();
        $job = SyncQueue::where('direction', SyncDirection::PushToTally)->firstOrFail();

        $this->withHeader('Authorization', 'Bearer agent-token-fail')
            ->postJson("/api/v1/integration/tally/agent/jobs/{$job->id}/result", [
                'success' => false,
                'error_code' => 'TALLY_INVALID_VOUCHER',
                'error_message' => 'Tally created 0 master(s) and reported 1 error(s).',
            ])
            ->assertOk();

        $this->assertNull($dealer->fresh()->tally_guid);
        $this->assertSame(0, \App\Models\TallyMapping::count());
    }

    public function test_a_push_job_carries_its_direction_so_the_agent_can_tell_them_apart(): void
    {
        // The agent routes on direction, not entity type — a dealer job is
        // either a pull or a push and the payload alone cannot say which.
        config()->set('sfa.tally.master_push_enabled', true);
        $this->connectionWithToken('agent-token-jobs');
        Dealer::factory()->create(['name' => 'Savar Pump House', 'tally_guid' => null, 'status' => true]);

        app(\App\Services\TallyMasterPushService::class)->enqueueAll();

        $this->withHeader('Authorization', 'Bearer agent-token-jobs')
            ->getJson('/api/v1/integration/tally/agent/jobs')
            ->assertOk()
            ->assertJsonPath('data.0.direction', 'push_to_tally')
            ->assertJsonPath('data.0.entity_type', 'dealer')
            ->assertJsonPath('data.0.payload.name', 'Savar Pump House');
    }
}
