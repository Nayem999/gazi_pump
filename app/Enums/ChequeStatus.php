<?php

declare(strict_types=1);

namespace App\Enums;

enum ChequeStatus: string
{
    case Collected = 'collected';
    case Submitted = 'submitted';
    case Deposited = 'deposited';
    case Cleared = 'cleared';
    case Bounced = 'bounced';

    public function label(): string
    {
        return match ($this) {
            self::Collected => 'Collected',
            self::Submitted => 'Submitted',
            self::Deposited => 'Deposited',
            self::Cleared => 'Cleared',
            self::Bounced => 'Bounced',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Collected => 'secondary',
            self::Submitted => 'info',
            self::Deposited => 'primary',
            self::Cleared => 'success',
            self::Bounced => 'danger',
        };
    }

    /**
     * The lifecycle only ever moves forward: Collected -> Submitted ->
     * Deposited -> (Cleared | Bounced), a dead end either way — no
     * "un-clearing" a cheque once the bank has actually settled it.
     *
     * @return array<int, self>
     */
    public function nextOptions(): array
    {
        return match ($this) {
            self::Collected => [self::Submitted],
            self::Submitted => [self::Deposited],
            self::Deposited => [self::Cleared, self::Bounced],
            self::Cleared, self::Bounced => [],
        };
    }
}
