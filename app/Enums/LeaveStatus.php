<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A leave request's lifecycle.
 *
 * Deliberately its own enum rather than reusing ApprovalStatus, which has
 * no Cancelled case: an employee withdrawing their own pending request is
 * a normal, frequent action here and is not the same event as a manager
 * rejecting it. Conflating the two would lose who ended the request and
 * why, which is exactly what an attendance dispute needs to establish.
 */
enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Cancelled => 'secondary',
        };
    }

    /**
     * Whether this status still occupies the employee's calendar.
     *
     * Only approved leave consumes a balance and blocks an overlapping
     * request; a pending one does neither, so two people cannot both be
     * told "you have days left" on the strength of the same request.
     */
    public function isSettled(): bool
    {
        return $this === self::Approved;
    }

    /** Statuses a manager can still act on. */
    public function isOpen(): bool
    {
        return $this === self::Pending;
    }
}
