<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReconciliationStatus;
use App\Enums\TallyEntityType;
use App\Enums\TallyMappingSyncStatus;
use App\Enums\TallyRecordSyncStatus;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\SalesReturn;
use App\Models\TallyMapping;
use Illuminate\Support\Collection;

/**
 * Phase 1 built the master-data comparison (Dealer/Retailer/Product vs.
 * their tally_mappings row); Phase 7 extends this rather than replacing it,
 * per this class's original doc comment, now that Order/Delivery/
 * CollectionEntry/SalesReturn and real Ledger data (Phase 5) all exist to
 * reconcile against.
 */
class TallyReconciliationService
{
    public function __construct(private readonly TallyLedgerSyncService $ledgerSync) {}

    /**
     * @return Collection<int, array{sfa_id: int, name: string, status: ReconciliationStatus, mapping: ?TallyMapping}>
     */
    public function compareDealers(): Collection
    {
        return $this->compare(TallyEntityType::Dealer, Dealer::query()->get(['id', 'name']));
    }

    /**
     * @return Collection<int, array{sfa_id: int, name: string, status: ReconciliationStatus, mapping: ?TallyMapping}>
     */
    public function compareRetailers(): Collection
    {
        return $this->compare(TallyEntityType::Retailer, Retailer::query()->get(['id', 'name']));
    }

    /**
     * @return Collection<int, array{sfa_id: int, name: string, status: ReconciliationStatus, mapping: ?TallyMapping}>
     */
    public function compareProducts(): Collection
    {
        return $this->compare(TallyEntityType::Product, Product::query()->get(['id', 'name']));
    }

    /**
     * For every dealer whose Tally ledger has actually been synced
     * (Phase 5), compares SFA's own Order/CollectionEntry-computed balance
     * against the real Tally-derived one — a real accounting reconciliation,
     * not just a mapping check. A mismatch beyond rounding means either an
     * order/collection SFA has that never reached Tally (still Pending/
     * Failed) or something Tally-side SFA doesn't know about (a manual
     * entry, a return not yet requested here).
     *
     * @return Collection<int, object{dealer: Dealer, sfa_balance: float, tally_balance: float, difference: float, status: ReconciliationStatus}>
     */
    public function compareDealerBalances(): Collection
    {
        return Dealer::query()
            ->whereHas('ledgerEntries')
            ->get()
            ->map(function (Dealer $dealer) {
                $tallyBalance = $this->ledgerSync->outstandingBalance($dealer) ?? 0.0;
                $sfaBalance = round(
                    (float) Order::where('dealer_id', $dealer->id)->sum('total_amount')
                        - (float) CollectionEntry::where('dealer_id', $dealer->id)->sum('amount'),
                    2,
                );
                $difference = round($sfaBalance - $tallyBalance, 2);

                return (object) [
                    'dealer' => $dealer,
                    'sfa_balance' => $sfaBalance,
                    'tally_balance' => $tallyBalance,
                    'difference' => $difference,
                    'status' => abs($difference) < 0.01 ? ReconciliationStatus::Matched : ReconciliationStatus::CustomerMismatch,
                ];
            })
            ->sortByDesc(fn ($row) => abs($row->difference))
            ->values();
    }

    /**
     * Every Order/Delivery/CollectionEntry/SalesReturn currently stuck in
     * `sync_status = Failed` — one place an admin can triage every kind of
     * sync failure at once, rather than checking four different list pages.
     *
     * @return array{orders: Collection, deliveries: Collection, collections: Collection, returns: Collection}
     */
    public function failedSyncs(): array
    {
        $failed = TallyRecordSyncStatus::Failed;

        return [
            'orders' => Order::where('sync_status', $failed)->with('dealer')->latest()->get(),
            'deliveries' => Delivery::where('sync_status', $failed)->with('order.dealer')->latest()->get(),
            'collections' => CollectionEntry::where('sync_status', $failed)->with('dealer')->latest()->get(),
            'returns' => SalesReturn::where('sync_status', $failed)->with('dealer')->latest()->get(),
        ];
    }

    /**
     * @param  Collection<int, Dealer|Retailer|Product>  $records
     * @return Collection<int, array{sfa_id: int, name: string, status: ReconciliationStatus, mapping: ?TallyMapping}>
     */
    private function compare(TallyEntityType $entityType, Collection $records): Collection
    {
        $mappingsBySfaId = TallyMapping::query()
            ->where('entity_type', $entityType)
            ->get()
            ->keyBy('sfa_id');

        return $records->map(function ($record) use ($mappingsBySfaId) {
            $mapping = $mappingsBySfaId->get($record->id);

            $status = match (true) {
                $mapping === null => ReconciliationStatus::NotSynced,
                $mapping->sync_status === TallyMappingSyncStatus::Conflict => ReconciliationStatus::CustomerMismatch,
                default => ReconciliationStatus::Matched,
            };

            return [
                'sfa_id' => $record->id,
                'name' => $record->name,
                'status' => $status,
                'mapping' => $mapping,
            ];
        });
    }
}
