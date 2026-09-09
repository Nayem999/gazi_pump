<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Models\Dealer;
use App\Models\Depot;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\SyncQueue;
use App\Models\TallyMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The SFA -> Tally half of master sync: records that exist here and have
 * no counterpart in the customer's accounting system.
 *
 * This is the only part of the integration that creates permanent masters
 * inside Tally — a Ledger in the chart of accounts, a Stock Item, a
 * Godown. Everything else either reads from Tally or writes a voucher an
 * operator already approved. Three consequences follow, and they are the
 * design:
 *
 *  1. **Off unless a deployment opts in** (`sfa.tally.master_push_enabled`).
 *     An accountant may reasonably consider the chart of accounts theirs
 *     to own, and that is not a decision this code should make silently.
 *  2. **Only active, human-owned records are eligible.** A record with no
 *     `tally_guid` but `status` false is almost always an inactive stub
 *     that TallyMasterSyncService imported *from* Tally and nobody has
 *     completed yet — pushing it back would create a duplicate of the
 *     master it came from.
 *  3. **Create only, never update.** Once a record has a GUID it is
 *     matched by GUID and left alone; SFA never pushes an edit over an
 *     accountant's own correction.
 *
 * The push is one queue row per record, not a batch, so one bad name
 * cannot block the rest — and `external_reference` is keyed on the record
 * rather than the clock, making a re-run idempotent for as long as the
 * record stays unmapped.
 */
class TallyMasterPushService
{
    public function __construct(
        private readonly TallySyncQueueService $queue,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('sfa.tally.master_push_enabled', false);
    }

    /**
     * Everything that would be pushed, without queueing anything — so the
     * operator can see what is about to be written into their accounting
     * system before it happens.
     *
     * @return array<string, \Illuminate\Support\Collection<int, Model>>
     */
    public function pending(): array
    {
        $pending = [];

        foreach (TallyEntityType::cases() as $entityType) {
            if ($this->modelFor($entityType) === null) {
                continue;
            }

            $records = $this->pushableFor($entityType);

            if ($records->isNotEmpty()) {
                $pending[$entityType->value] = $records;
            }
        }

        return $pending;
    }

    /**
     * Queues a create for every eligible unmapped record.
     *
     * @return array{queued: int, skipped: array<int, string>, disabled: bool}
     */
    public function enqueueAll(): array
    {
        if (! $this->enabled()) {
            return ['queued' => 0, 'skipped' => [], 'disabled' => true];
        }

        $queued = 0;
        $skipped = [];

        foreach (TallyEntityType::cases() as $entityType) {
            if ($this->modelFor($entityType) === null) {
                continue;
            }

            foreach ($this->pushableFor($entityType) as $record) {
                $reason = $this->blockedReason($entityType, $record);

                if ($reason !== null) {
                    $skipped[] = $record->name.' — '.$reason;

                    continue;
                }

                if ($this->enqueuePush($entityType, $record)) {
                    $queued++;
                }
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped, 'disabled' => false];
    }

    /**
     * One queue row for one record, idempotent while it stays unmapped.
     */
    public function enqueuePush(TallyEntityType $entityType, Model $record): ?SyncQueue
    {
        $alreadyQueued = SyncQueue::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $record->id)
            ->where('direction', SyncDirection::PushToTally)
            ->whereIn('status', [SyncStatus::Pending, SyncStatus::Processing])
            ->exists();

        if ($alreadyQueued) {
            return null;
        }

        return $this->queue->enqueue(
            $entityType,
            (int) $record->id,
            SyncDirection::PushToTally,
            sprintf('SFA-MPUSH-%s-%d', $entityType->value, $record->id),
            $this->payloadFor($entityType, $record),
        );
    }

    /**
     * Writes the GUID Tally assigned to a pushed master back onto the SFA
     * record, completing the round trip.
     *
     * Without this the record stays unmapped and the next push would try
     * to create it again — colliding on the name, since Tally rejects
     * duplicate master names.
     */
    public function applyPushResult(SyncQueue $syncQueue, string $tallyGuid): bool
    {
        $modelClass = $this->modelFor($syncQueue->entity_type);

        if ($modelClass === null || ! $syncQueue->entity_id || trim($tallyGuid) === '') {
            return false;
        }

        /** @var Model|null $record */
        $record = $modelClass::query()->find($syncQueue->entity_id);

        if (! $record) {
            return false;
        }

        return DB::transaction(function () use ($record, $syncQueue, $tallyGuid) {
            $record->update(['tally_guid' => $tallyGuid]);

            TallyMapping::updateOrCreate(
                ['entity_type' => $syncQueue->entity_type, 'sfa_id' => $record->id],
                ['tally_guid' => $tallyGuid, 'tally_name' => $record->name, 'last_synced_at' => now()],
            );

            return true;
        });
    }

    /**
     * Unmapped, active records of one type.
     *
     * The `status` filter is what keeps an imported-from-Tally stub from
     * being pushed straight back as a duplicate — see the class note.
     *
     * @return \Illuminate\Support\Collection<int, Model>
     */
    private function pushableFor(TallyEntityType $entityType): \Illuminate\Support\Collection
    {
        $modelClass = $this->modelFor($entityType);

        return $modelClass::query()
            ->whereNull('tally_guid')
            ->where('status', true)
            ->orderBy('id')
            ->get();
    }

    /**
     * Why this record cannot be pushed, or null if it can.
     *
     * A missing group is a configuration gap rather than a data problem,
     * and it is worth reporting per record instead of failing the whole
     * run: a ledger pushed into a group the pull side does not read would
     * leave Tally and never come back.
     */
    private function blockedReason(TallyEntityType $entityType, Model $record): ?string
    {
        if (trim((string) $record->name) === '') {
            return 'it has no name to create in Tally';
        }

        if ($entityType === TallyEntityType::Retailer && blank($this->retailerGroup())) {
            return 'SFA_TALLY_RETAILER_LEDGER_GROUP is not configured, so there is no group to file it under';
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(TallyEntityType $entityType, Model $record): array
    {
        return match ($entityType) {
            TallyEntityType::Dealer => [
                'name' => $record->name,
                'group' => $this->dealerGroup(),
                'phone' => $record->phone,
                'email' => $record->email,
                'address' => $record->address,
            ],
            TallyEntityType::Retailer => [
                'name' => $record->name,
                'group' => $this->retailerGroup(),
                'phone' => $record->phone,
                'email' => $record->email,
                'address' => $record->shipping_address,
            ],
            TallyEntityType::Product => [
                'name' => $record->name,
                'group' => (string) config('sfa.tally.stock_item_group', ''),
                'unit' => (string) config('sfa.tally.stock_item_unit', 'PCS'),
            ],
            TallyEntityType::Depot => [
                'name' => $record->name,
                'address' => $record->address,
            ],
            default => [],
        };
    }

    private function dealerGroup(): string
    {
        return (string) config('sfa.tally.dealer_ledger_group', 'Sundry Debtors');
    }

    private function retailerGroup(): ?string
    {
        $group = config('sfa.tally.retailer_ledger_group');

        return is_string($group) && trim($group) !== '' ? trim($group) : null;
    }

    /**
     * @return class-string<Model>|null
     */
    private function modelFor(TallyEntityType $entityType): ?string
    {
        return match ($entityType) {
            TallyEntityType::Dealer => Dealer::class,
            TallyEntityType::Retailer => Retailer::class,
            TallyEntityType::Product => Product::class,
            TallyEntityType::Depot => Depot::class,
            default => null,
        };
    }
}
