<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Structured error classification for a failed sync_queues row, so the
 * admin Integration Dashboard can show a meaningful reason instead of a raw
 * exception string, while the raw response/error text is still preserved
 * separately (sync_queues.response / error_message) for debugging.
 */
enum TallySyncErrorCode: string
{
    case TallyOffline = 'TALLY_OFFLINE';
    case TallyTimeout = 'TALLY_TIMEOUT';
    case TallyCompanyNotFound = 'TALLY_COMPANY_NOT_FOUND';
    case TallyLedgerNotFound = 'TALLY_LEDGER_NOT_FOUND';
    case TallyProductNotFound = 'TALLY_PRODUCT_NOT_FOUND';
    case TallyInvalidVoucher = 'TALLY_INVALID_VOUCHER';
    case StockNotAvailable = 'STOCK_NOT_AVAILABLE';
    case DuplicateTransaction = 'DUPLICATE_TRANSACTION';
    case CustomerMappingMissing = 'CUSTOMER_MAPPING_MISSING';
    case ProductMappingMissing = 'PRODUCT_MAPPING_MISSING';
    case InvalidAmount = 'INVALID_AMOUNT';
    case InvalidQuantity = 'INVALID_QUANTITY';

    public function label(): string
    {
        return match ($this) {
            self::TallyOffline => 'Tally is offline',
            self::TallyTimeout => 'Tally did not respond in time',
            self::TallyCompanyNotFound => 'Tally company not found',
            self::TallyLedgerNotFound => 'Tally ledger not found',
            self::TallyProductNotFound => 'Tally product not found',
            self::TallyInvalidVoucher => 'Invalid Tally voucher',
            self::StockNotAvailable => 'Stock not available',
            self::DuplicateTransaction => 'Duplicate transaction',
            self::CustomerMappingMissing => 'Customer mapping missing',
            self::ProductMappingMissing => 'Product mapping missing',
            self::InvalidAmount => 'Invalid amount',
            self::InvalidQuantity => 'Invalid quantity',
        };
    }

    /**
     * Whether this failure is worth retrying automatically (a transient
     * connectivity/timing problem) versus one that will fail identically
     * every time until a human fixes the underlying data (a missing mapping,
     * an invalid amount, etc.).
     */
    public function isRetryable(): bool
    {
        return match ($this) {
            self::TallyOffline, self::TallyTimeout => true,
            default => false,
        };
    }
}
