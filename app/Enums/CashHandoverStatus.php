<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A cash handover's lifecycle: an executive records that they've handed
 * cash to a manager (Pending), and that manager either Confirms receipt or
 * Rejects it (e.g. the amount doesn't match what was physically handed
 * over). Both are terminal — a rejected handover is recreated, not reopened.
 */
enum CashHandoverStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'success',
            self::Rejected => 'danger',
        };
    }
}
