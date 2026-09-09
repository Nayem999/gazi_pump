<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\LedgerEntry;
use App\Models\SyncQueue;
use App\Services\TallyLedgerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TallyLedgerSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TallyLedgerSyncService
    {
        return app(TallyLedgerSyncService::class);
    }

    /**
     * The real "GDN Tally" company runs 1-Sep to 31-Aug, so a pull must
     * start at the September opening, not 1 January — Tally serves nothing
     * outside its own active period.
     */
    public function test_financial_year_start_uses_the_configured_opening_month(): void
    {
        config(['sfa.tally.financial_year_start_month' => 9]);

        // Already past this year's September opening.
        $this->assertSame(
            '2026-09-01',
            $this->service()->financialYearStart(Carbon::parse('2026-09-07'))->toDateString(),
        );

        // Before it — the year rolls back to the previous opening.
        $this->assertSame(
            '2025-09-01',
            $this->service()->financialYearStart(Carbon::parse('2026-08-31'))->toDateString(),
        );
    }

    public function test_financial_year_start_honours_a_different_opening_month(): void
    {
        config(['sfa.tally.financial_year_start_month' => 4]);

        $this->assertSame(
            '2026-04-01',
            $this->service()->financialYearStart(Carbon::parse('2026-09-07'))->toDateString(),
        );
    }

    public function test_enqueue_pull_requires_the_dealer_to_have_a_tally_mapping(): void
    {
        $dealer = Dealer::factory()->create(['tally_guid' => null]);

        $this->expectException(ValidationException::class);

        $this->service()->enqueuePull($dealer, '2026-01-01', '2026-09-07');
    }

    public function test_enqueue_pull_creates_a_pending_ledger_pull_job(): void
    {
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1', 'tally_ledger_name' => 'ABC Traders']);

        $this->service()->enqueuePull($dealer, '2026-01-01', '2026-09-07');

        $this->assertDatabaseHas('sync_queues', [
            'entity_type' => TallyEntityType::Ledger->value,
            'entity_id' => $dealer->id,
            'direction' => SyncDirection::PullFromTally->value,
        ]);

        $job = SyncQueue::where('entity_id', $dealer->id)->firstOrFail();
        $this->assertSame('ABC Traders', $job->payload['dealer_tally_name']);
        $this->assertSame('2026-01-01', $job->payload['from_date']);
    }

    public function test_apply_pulled_entries_upserts_by_tally_guid_and_skips_rows_without_one(): void
    {
        $dealer = Dealer::factory()->create();

        $result = $this->service()->applyPulledEntries($dealer->id, [
            ['tally_guid' => 'V-1', 'voucher_date' => '2026-08-01', 'voucher_type' => 'Sales', 'voucher_number' => 'SV-1', 'debit_amount' => 1000, 'credit_amount' => 0, 'narration' => null],
            ['tally_guid' => null, 'voucher_date' => '2026-08-02', 'voucher_type' => 'Sales', 'voucher_number' => 'SV-2', 'debit_amount' => 500, 'credit_amount' => 0, 'narration' => null],
        ]);

        $this->assertSame(['applied' => 1, 'skipped' => 1], $result);
        $this->assertSame(1, LedgerEntry::count());

        // Re-applying the same row (e.g. a repeated pull) updates in place,
        // never duplicates.
        $this->service()->applyPulledEntries($dealer->id, [
            ['tally_guid' => 'V-1', 'voucher_date' => '2026-08-01', 'voucher_type' => 'Sales', 'voucher_number' => 'SV-1', 'debit_amount' => 1200, 'credit_amount' => 0, 'narration' => 'corrected'],
        ]);

        $this->assertSame(1, LedgerEntry::count());
        $entry = LedgerEntry::where('tally_guid', 'V-1')->firstOrFail();
        $this->assertSame(1200.0, (float) $entry->debit_amount);
        $this->assertSame('corrected', $entry->narration);
    }

    /**
     * Fixture captured verbatim from the real "GDN Tally" instance on
     * 2026-09-07 — a Journal dated 1-Sep-2026 that credits "Dealer 02" 50
     * and debits "Supplier 1" 50. This is the exact row shape the Sync
     * Agent produces (tally-sync-agent/src/ledgerSnapshot.js), so if the
     * two sides ever drift apart, this fails.
     *
     * Note `ledger_name` and the `-1` GUID suffix: one voucher moves several
     * ledgers, so the agent emits one row per ledger entry and makes each
     * row's tally_guid unique per entry index — a bare voucher GUID would
     * collapse both sides of a double-entry into a single upserted row.
     */
    public function test_it_stores_a_real_captured_tally_journal_row(): void
    {
        $dealer = Dealer::factory()->create(['tally_guid' => 'd530416d-350a-4df9-bc3c-06943927816a-000000d6']);

        $result = $this->service()->applyPulledEntries($dealer->id, [[
            'tally_guid' => 'd530416d-350a-4df9-bc3c-06943927816a-00000001-1',
            'ledger_name' => 'Dealer 02',
            'voucher_date' => '2026-09-01',
            'voucher_type' => 'Journal',
            'voucher_number' => '1',
            'narration' => null,
            'debit_amount' => 0,
            'credit_amount' => 50,
        ]]);

        $this->assertSame(['applied' => 1, 'skipped' => 0], $result);

        $entry = LedgerEntry::where('dealer_id', $dealer->id)->firstOrFail();
        $this->assertSame('2026-09-01', $entry->voucher_date->toDateString());
        $this->assertSame('Journal', $entry->voucher_type);
        $this->assertSame(0.0, (float) $entry->debit_amount);
        $this->assertSame(50.0, (float) $entry->credit_amount);

        // A Journal credit reduces what the dealer owes.
        $this->assertSame(-50.0, $this->service()->outstandingBalance($dealer));
    }

    public function test_outstanding_balance_is_null_when_never_synced_and_real_once_it_is(): void
    {
        $dealer = Dealer::factory()->create();

        $this->assertNull($this->service()->outstandingBalance($dealer));

        LedgerEntry::factory()->create(['dealer_id' => $dealer->id, 'debit_amount' => 1000, 'credit_amount' => 0]);
        LedgerEntry::factory()->create(['dealer_id' => $dealer->id, 'debit_amount' => 0, 'credit_amount' => 300]);

        $this->assertSame(700.0, $this->service()->outstandingBalance($dealer));
    }
}
