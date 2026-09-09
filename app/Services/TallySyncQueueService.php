<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Enums\TallySyncErrorCode;
use App\Models\SyncLog;
use App\Models\SyncQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Every outbound/inbound Tally transaction passes through here (spec §29).
 * enqueue() is the idempotency boundary (§30) — a retry-safe caller always
 * enqueues with the same external_reference and this never creates a second
 * row for it, so a retried request can never double-create a Tally voucher.
 */
class TallySyncQueueService
{
    public function __construct(private readonly TallyRetryService $retry) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(
        TallyEntityType $entityType,
        ?int $entityId,
        SyncDirection $direction,
        string $externalReference,
        array $payload,
    ): SyncQueue {
        return SyncQueue::query()->firstOrCreate(
            ['external_reference' => $externalReference],
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'direction' => $direction,
                'payload' => $payload,
                'status' => SyncStatus::Pending,
            ],
        );
    }

    /**
     * Queues a Tally→SFA pull of one master list (Dealer/Retailer/Product/
     * Depot ledger, stock-item or godown lists).
     *
     * Skipped when a pull for the same entity type is already waiting: the
     * "Sync Now" button is the kind of thing an impatient admin clicks
     * repeatedly, and a master pull re-reads everything anyway, so a second
     * identical job would only add queue noise and Tally round-trips.
     * Returns null when it skipped.
     */
    public function enqueueMasterPull(TallyEntityType $entityType): ?SyncQueue
    {
        $alreadyQueued = SyncQueue::query()
            ->where('entity_type', $entityType)
            ->where('direction', SyncDirection::PullFromTally)
            ->whereIn('status', [SyncStatus::Pending, SyncStatus::Processing])
            ->exists();

        if ($alreadyQueued) {
            return null;
        }

        return $this->enqueue(
            $entityType,
            null,
            SyncDirection::PullFromTally,
            sprintf('SFA-PULL-%s-%s', $entityType->value, now()->format('YmdHis')),
            [],
        );
    }

    /**
     * Claims up to $limit Pending rows for the Sync Agent's next poll,
     * locking them so two concurrent polls (or a poll racing the retry
     * command) can never claim the same row twice.
     *
     * @return Collection<int, SyncQueue>
     */
    public function claimNext(int $limit = 10): Collection
    {
        return DB::transaction(function () use ($limit) {
            $items = SyncQueue::query()
                ->where('status', SyncStatus::Pending)
                ->oldest('id')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            $items->each(function (SyncQueue $item) {
                $item->update([
                    'status' => SyncStatus::Processing,
                    'attempt_count' => $item->attempt_count + 1,
                    'last_attempt_at' => now(),
                ]);
            });

            return $items;
        });
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function markSuccess(SyncQueue $item, array $response = [], ?string $tallyGuid = null, ?string $tallyVoucherNumber = null): SyncQueue
    {
        $now = now();

        $item->update([
            'status' => SyncStatus::Success,
            'response' => $response,
            'completed_at' => $now,
        ]);

        SyncLog::create([
            'entity_type' => $item->entity_type,
            'entity_id' => $item->entity_id,
            'direction' => $item->direction,
            'request_time' => $item->last_attempt_at ?? $now,
            'response_time' => $now,
            'status' => SyncStatus::Success->value,
            'external_reference' => $item->external_reference,
            'tally_guid' => $tallyGuid,
            'tally_voucher_number' => $tallyVoucherNumber,
        ]);

        return $item->refresh();
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function markFailed(SyncQueue $item, TallySyncErrorCode $errorCode, string $errorMessage, array $response = []): SyncQueue
    {
        $now = now();
        $canRetry = $errorCode->isRetryable() && $this->retry->hasAttemptsRemaining($item->attempt_count);

        $item->update([
            'status' => $canRetry ? SyncStatus::Retry : SyncStatus::Failed,
            'next_attempt_at' => $canRetry ? $this->retry->nextAttemptAt($item->attempt_count) : null,
            'response' => $response,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'completed_at' => $canRetry ? null : $now,
        ]);

        SyncLog::create([
            'entity_type' => $item->entity_type,
            'entity_id' => $item->entity_id,
            'direction' => $item->direction,
            'request_time' => $item->last_attempt_at ?? $now,
            'response_time' => $now,
            'status' => $canRetry ? SyncStatus::Retry->value : SyncStatus::Failed->value,
            'external_reference' => $item->external_reference,
            'error_message' => $errorMessage,
        ]);

        return $item->refresh();
    }

    /**
     * @param  array{entity_type?: string, status?: string, direction?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return SyncQueue::query()
            ->when($filters['entity_type'] ?? null, fn ($query, $type) => $query->where('entity_type', $type))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['direction'] ?? null, fn ($query, $direction) => $query->where('direction', $direction))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
