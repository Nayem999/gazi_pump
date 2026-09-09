<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * State of one tally_mappings row itself — distinct from SyncStatus, which
 * tracks a single queue job's lifecycle. A mapping stays Synced indefinitely
 * once matched; Conflict means the SFA and Tally sides disagree (e.g. a
 * name mismatch) and needs an admin to resolve it (spec: "data consistency"
 * — never silently overwrite, always route conflicts to a resolution
 * screen).
 */
enum TallyMappingSyncStatus: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';
    case Conflict = 'conflict';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Synced => 'Synced',
            self::Failed => 'Failed',
            self::Conflict => 'Conflict',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Synced => 'success',
            self::Failed => 'danger',
            self::Conflict => 'warning',
        };
    }
}
