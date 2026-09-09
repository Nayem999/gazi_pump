<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SyncStatus;
use App\Models\SyncQueue;
use Illuminate\Support\Carbon;

/**
 * The retry backoff table (spec §31): attempt 1 is immediate, then 1/5/15/30
 * minutes, then the row is left Failed for good — an admin must retry it
 * manually from the Sync Queue screen at that point (TallyRetryService
 * doesn't reset attempt_count itself; TallySyncQueueService::enqueue()'s
 * caller decides whether a fresh attempt counter is warranted).
 */
class TallyRetryService
{
    /**
     * @var array<int, int> attempt number => delay in minutes before the next try
     */
    private const BACKOFF_MINUTES = [
        1 => 0,
        2 => 1,
        3 => 5,
        4 => 15,
        5 => 30,
    ];

    private const MAX_ATTEMPTS = 5;

    public function nextAttemptAt(int $attemptCountAfterThisFailure): ?Carbon
    {
        if (! isset(self::BACKOFF_MINUTES[$attemptCountAfterThisFailure])) {
            return null;
        }

        return Carbon::now()->addMinutes(self::BACKOFF_MINUTES[$attemptCountAfterThisFailure]);
    }

    public function hasAttemptsRemaining(int $attemptCount): bool
    {
        return $attemptCount < self::MAX_ATTEMPTS;
    }

    /**
     * Invoked by the tally:process-retries scheduled command: flips every
     * due Retry row back to Pending so the Sync Agent's next poll picks it
     * up naturally through the normal claim path, rather than dispatching
     * anything itself.
     */
    public function processDue(): int
    {
        return SyncQueue::query()
            ->where('status', SyncStatus::Retry)
            ->where('next_attempt_at', '<=', Carbon::now())
            ->update(['status' => SyncStatus::Pending->value, 'next_attempt_at' => null]);
    }

    /**
     * An admin explicitly asking a Failed row to be tried again — resets
     * the attempt counter for a clean new run of the backoff table.
     */
    public function manualRetry(SyncQueue $queueItem): SyncQueue
    {
        $queueItem->update([
            'status' => SyncStatus::Pending,
            'attempt_count' => 0,
            'next_attempt_at' => null,
            'error_code' => null,
            'error_message' => null,
        ]);

        return $queueItem->refresh();
    }
}
