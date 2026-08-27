<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Shared by Order and CollectionEntry: every record starts Pending and a
 * manager either Approves or Rejects it. Both are terminal — a rejected
 * record is corrected and resubmitted (edited, which leaves status alone),
 * not reopened through this enum.
 */
enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
