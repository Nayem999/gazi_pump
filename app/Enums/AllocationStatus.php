<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * One order_item's depot-allocation state. Partial means some quantity was
 * allocated but the requested_qty wasn't fully covered even after checking
 * alternative depots (spec §12) — a manager can still approve dispatching
 * the partial amount, or reject and hold the line for restock.
 */
enum AllocationStatus: string
{
    case Pending = 'pending';
    case Allocated = 'allocated';
    case PartiallyAllocated = 'partially_allocated';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Allocated => 'Allocated',
            self::PartiallyAllocated => 'Partially Allocated',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Allocated => 'success',
            self::PartiallyAllocated => 'warning',
            self::Rejected => 'danger',
        };
    }
}
