<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Values match Carbon's own `format('l')` output exactly, so a future
 * consumer (e.g. "is today a weekend?") can compare
 * `now()->format('l')` against the configured weekend days with no mapping
 * layer needed.
 */
enum DayOfWeek: string
{
    case Sunday = 'Sunday';
    case Monday = 'Monday';
    case Tuesday = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday = 'Thursday';
    case Friday = 'Friday';
    case Saturday = 'Saturday';
}
