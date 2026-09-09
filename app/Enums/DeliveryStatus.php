<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A Delivery/Challan's own business status — separate from sync_status
 * (App\Enums\TallyRecordSyncStatus), same two-dimensional status model as
 * Order/CollectionEntry (spec §46). A Delivery is created already
 * Dispatched (see DeliveryService::dispatch()) — there is no draft/pending
 * state, since the act of creating the row *is* the dispatch decision.
 */
enum DeliveryStatus: string
{
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Dispatched => 'info',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
