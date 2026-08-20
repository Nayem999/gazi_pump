<?php

declare(strict_types=1);

namespace App\Enums;

enum VisitPlanStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Planned => 'info',
            self::Completed => 'success',
            self::Cancelled => 'secondary',
        };
    }
}
