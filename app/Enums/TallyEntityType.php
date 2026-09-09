<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Every kind of record the Tally integration knows how to map/queue/log.
 * Shared across tally_mappings, sync_queues, and sync_logs so all three
 * tables classify entities the same way. Only Dealer/Retailer/Product are
 * wired to real SFA models in Phase 1 (see docs/tally-sfa-integration.md,
 * Phase 1) — the rest exist here now so later phases don't need a schema
 * change, just new code paths.
 */
enum TallyEntityType: string
{
    case Dealer = 'dealer';
    case Retailer = 'retailer';
    case Product = 'product';
    case Depot = 'depot';
    case SalesOrder = 'sales_order';
    case Invoice = 'invoice';
    case Delivery = 'delivery';
    case Collection = 'collection';
    case SalesReturn = 'return';
    case CreditNote = 'credit_note';
    case DebitNote = 'debit_note';
    case Ledger = 'ledger';

    public function label(): string
    {
        return match ($this) {
            self::Dealer => 'Dealer',
            self::Retailer => 'Retailer',
            self::Product => 'Product',
            self::Depot => 'Depot',
            self::SalesOrder => 'Sales Order',
            self::Invoice => 'Invoice',
            self::Delivery => 'Delivery',
            self::Collection => 'Collection',
            self::SalesReturn => 'Sales Return',
            self::CreditNote => 'Credit Note',
            self::DebitNote => 'Debit Note',
            self::Ledger => 'Ledger',
        };
    }
}
