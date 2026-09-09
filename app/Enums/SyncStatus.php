<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle of one sync_queues row. Pending -> Processing (claimed by the
 * Sync Agent) -> Success | Retry (a transient failure, will be retried per
 * TallyRetryService's backoff table) -> Failed (retries exhausted) or
 * Cancelled (an admin withdrew it before it ran). This is deliberately
 * separate from any business status (e.g. an Order's own Draft/Submitted/
 * Approved) — see the "transaction status model" principle: business state
 * and sync state must never collapse into one column.
 */
enum SyncStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';
    case Retry = 'retry';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Retry => 'Retrying',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Processing => 'info',
            self::Success => 'success',
            self::Failed => 'danger',
            self::Retry => 'warning',
            self::Cancelled => 'dark',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Success, self::Failed, self::Cancelled => true,
            default => false,
        };
    }
}
