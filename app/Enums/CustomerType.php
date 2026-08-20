<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerType: string
{
    case Dealer = 'dealer';
    case Retailer = 'retailer';
    case Distributor = 'distributor';

    public function label(): string
    {
        return match ($this) {
            self::Dealer => 'Dealer',
            self::Retailer => 'Retailer',
            self::Distributor => 'Distributor',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Dealer => 'primary',
            self::Retailer => 'success',
            self::Distributor => 'warning',
        };
    }
}
