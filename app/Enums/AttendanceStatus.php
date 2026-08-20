<?php

declare(strict_types=1);

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case HalfDay = 'half_day';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Late => 'Late',
            self::HalfDay => 'Half Day',
            self::Absent => 'Absent',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Late => 'warning',
            self::HalfDay => 'info',
            self::Absent => 'danger',
        };
    }
}
