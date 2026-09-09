<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * TallyPrime versions differ in which request/response format their HTTP
 * gateway accepts well — older/some installs are XML-only, newer ones can
 * also speak JSON. Stored per-connection rather than assumed globally so the
 * integration never hardcodes a single Tally version (see the Sync Agent
 * architecture notes).
 */
enum TallyApiFormat: string
{
    case Json = 'json';
    case Xml = 'xml';

    public function label(): string
    {
        return match ($this) {
            self::Json => 'JSON',
            self::Xml => 'XML',
        };
    }
}
