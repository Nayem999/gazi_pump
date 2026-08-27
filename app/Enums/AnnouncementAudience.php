<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnouncementAudience: string
{
    case All = 'all';
    case Role = 'role';
    case Territory = 'territory';
    case User = 'user';
    case AllDealers = 'all_dealers';
    case Dealer = 'dealer';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Everyone',
            self::Role => 'Specific Role',
            self::Territory => 'Specific Territory',
            self::User => 'Specific User',
            self::AllDealers => 'All Dealers',
            self::Dealer => 'Specific Dealer',
        };
    }
}
