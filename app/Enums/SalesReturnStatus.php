<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A Sales Return's own business status, separate from sync_status
 * (TallyRecordSyncStatus) — same two-dimensional model as Order/
 * CollectionEntry/Delivery. The workflow is forward-only: Requested ->
 * (Approved -> Dispatched -> Received) | Rejected. Only a Received return
 * is ever pushed toward Tally as a Credit Note.
 */
enum SalesReturnStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Dispatched = 'dispatched';
    case Received = 'received';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Dispatched => 'Dispatched',
            self::Received => 'Received',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Requested => 'warning',
            self::Approved => 'info',
            self::Rejected => 'danger',
            self::Dispatched => 'primary',
            self::Received => 'success',
        };
    }
}
