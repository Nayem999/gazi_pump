<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The sync-status half of a Tally-bound record's two-dimensional status
 * model (spec §46: never collapse business status and sync status into one
 * column). An Order/CollectionEntry's existing `status`
 * (App\Enums\ApprovalStatus — Pending/Approved/Rejected) is its business
 * status and is untouched by this; this enum only ever describes whether
 * *that already-decided* record has made it to Tally yet.
 */
enum TallyRecordSyncStatus: string
{
    case NotSynced = 'not_synced';
    case Pending = 'pending';
    case Syncing = 'syncing';
    case Synced = 'synced';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NotSynced => 'Not Synced',
            self::Pending => 'Pending',
            self::Syncing => 'Syncing',
            self::Synced => 'Synced',
            self::Failed => 'Sync Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NotSynced => 'secondary',
            self::Pending, self::Syncing => 'info',
            self::Synced => 'success',
            self::Failed => 'danger',
        };
    }
}
