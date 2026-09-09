<?php

declare(strict_types=1);

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case HalfDay = 'half_day';
    case Absent = 'absent';

    /**
     * Written by the Leave module when a request is approved, never by a
     * check-in. It is distinct from Absent on purpose: an approved absence
     * is not a no-show, and reports must not hold it against the person
     * (see ReportService::attendanceSummary, which excludes leave days
     * from the attendance-rate denominator rather than counting them
     * against it).
     */
    case Leave = 'leave';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Late => 'Late',
            self::HalfDay => 'Half Day',
            self::Absent => 'Absent',
            self::Leave => 'On Leave',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Late => 'warning',
            self::HalfDay => 'info',
            self::Absent => 'danger',
            self::Leave => 'primary',
        };
    }

    /**
     * Whether this row records the person actually working that day.
     *
     * Leave is excluded, which is what keeps approved leave out of the
     * attendance rate on both sides of the fraction.
     */
    public function isWorked(): bool
    {
        return in_array($this, [self::Present, self::Late, self::HalfDay], true);
    }
}
