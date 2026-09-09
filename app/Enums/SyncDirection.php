<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Which way one sync_queues row is moving data. Every module in the
 * synchronization matrix is fixed to exactly one direction except Dealer,
 * Retailer, and the Retailer<->Dealer transaction, which use both.
 */
enum SyncDirection: string
{
    case PushToTally = 'push_to_tally';
    case PullFromTally = 'pull_from_tally';

    public function label(): string
    {
        return match ($this) {
            self::PushToTally => 'SFA → Tally',
            self::PullFromTally => 'Tally → SFA',
        };
    }
}
