<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Enums\SyncStatus;
use App\Models\Dealer;
use App\Models\LedgerEntry;
use App\Models\SyncQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 5: Tally is the sole source of truth for a dealer's real ledger —
 * unlike Order/CollectionEntry (SFA-authored, pushed to Tally), a ledger
 * entry is authored in Tally and only ever pulled. Each dealer's pull is
 * its own sync_queues job (entity_type=Ledger, entity_id=dealer_id) since,
 * unlike stock, a ledger pull is naturally scoped to one dealer already —
 * no bespoke bulk endpoint needed the way stock-sync required one.
 */
class TallyLedgerSyncService
{
    public function __construct(private readonly TallySyncQueueService $syncQueue) {}

    /**
     * The opening date of the Tally financial year that today falls in.
     *
     * Tally only serves data inside the company's own active period, so a
     * pull asking for dates before this returns nothing useful. This
     * customer's company runs 1-Sep to 31-Aug (config
     * `sfa.tally.financial_year_start_month`), so on 7-Sep-2026 this is
     * 1-Sep-2026, and on 7-Aug-2026 it would still be 1-Sep-2025 — the
     * year rolls back whenever today is before the opening month.
     */
    public function financialYearStart(?Carbon $today = null): Carbon
    {
        $today ??= Carbon::today();
        $startMonth = (int) config('sfa.tally.financial_year_start_month', 1);

        // Built day-first rather than ->month($startMonth) on today's own
        // date: on the 31st, switching to a 30-day month overflows into the
        // next one (31-Aug -> "31-Sep" -> 1-Oct), which silently shifted the
        // whole financial year.
        $start = Carbon::create($today->year, $startMonth, 1)->startOfDay();

        return $start->greaterThan($today) ? $start->subYear() : $start;
    }

    /**
     * True when this dealer already has a ledger pull waiting or running,
     * so a bulk "Sync Now" doesn't queue the same dealer twice.
     */
    public function hasPullQueued(Dealer $dealer): bool
    {
        return SyncQueue::query()
            ->where('entity_type', TallyEntityType::Ledger)
            ->where('entity_id', $dealer->id)
            ->whereIn('status', [SyncStatus::Pending, SyncStatus::Processing])
            ->exists();
    }

    public function enqueuePull(Dealer $dealer, string $fromDate, string $toDate): void
    {
        if (! $dealer->tally_guid) {
            throw ValidationException::withMessages([
                'dealer' => 'This dealer has no Tally mapping (tally_guid) yet.',
            ]);
        }

        $this->syncQueue->enqueue(
            TallyEntityType::Ledger,
            $dealer->id,
            SyncDirection::PullFromTally,
            sprintf('SFA-LED-PULL-%d-%s', $dealer->id, now()->format('YmdHis')),
            [
                'dealer_tally_guid' => $dealer->tally_guid,
                'dealer_tally_name' => $dealer->tally_ledger_name ?: $dealer->name,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        );
    }

    /**
     * Upserts every pulled voucher row by tally_guid (idempotent — re-
     * pulling the same period never duplicates a row). A row with no
     * tally_guid can't be matched to anything on a later pull, so it's
     * skipped and counted rather than inserted as an un-reconcilable row.
     *
     * @param  array<int, array{tally_guid: ?string, voucher_date: string, voucher_type: string, voucher_number: ?string, debit_amount: float, credit_amount: float, narration: ?string}>  $rows
     * @return array{applied: int, skipped: int}
     */
    public function applyPulledEntries(int $dealerId, array $rows): array
    {
        return DB::transaction(function () use ($dealerId, $rows) {
            $applied = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                if (empty($row['tally_guid'])) {
                    $skipped++;

                    continue;
                }

                LedgerEntry::updateOrCreate(
                    ['tally_guid' => $row['tally_guid']],
                    [
                        'dealer_id' => $dealerId,
                        'voucher_date' => $row['voucher_date'],
                        'voucher_type' => $row['voucher_type'],
                        'voucher_number' => $row['voucher_number'] ?? null,
                        'debit_amount' => $row['debit_amount'] ?? 0,
                        'credit_amount' => $row['credit_amount'] ?? 0,
                        'narration' => $row['narration'] ?? null,
                        'synced_at' => now(),
                    ],
                );

                $applied++;
            }

            return ['applied' => $applied, 'skipped' => $skipped];
        });
    }

    /**
     * The real Tally-sourced due amount — null when this dealer has never
     * had a ledger pull, so a caller can fall back to the SFA-computed
     * estimate (Order/CollectionEntry) rather than reporting a false zero.
     */
    public function outstandingBalance(Dealer $dealer): ?float
    {
        if (! $dealer->ledgerEntries()->exists()) {
            return null;
        }

        $totals = $dealer->ledgerEntries()->selectRaw('SUM(debit_amount) as debit, SUM(credit_amount) as credit')->first();

        return round((float) $totals->debit - (float) $totals->credit, 2);
    }
}
