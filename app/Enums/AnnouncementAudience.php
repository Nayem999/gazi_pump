<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnouncementAudience: string
{
    case All = 'all';
    case Role = 'role';
    case Territory = 'territory';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Everyone',
            self::Role => 'Specific Role',
            self::Territory => 'Specific Territory',
            self::User => 'Specific User',
        };
    }
}
