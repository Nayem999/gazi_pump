<?php

declare(strict_types=1);

namespace App\Enums;

enum PerformanceGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case F = 'F';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Excellent',
            self::B => 'Good',
            self::C => 'Average',
            self::D => 'Below Average',
            self::F => 'Poor',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::A => 'success',
            self::B => 'info',
            self::C => 'warning',
            self::D => 'secondary',
            self::F => 'danger',
        };
    }
}
