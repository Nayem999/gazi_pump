<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Full status vocabulary from spec §35. Phase 1's TallyReconciliationService
 * only ever produces Matched/NotSynced/Conflict (Dealer/Retailer/Product
 * against their tally_mappings row) — the Tally-side-only and
 * amount/quantity/customer-mismatch cases need real Tally-side listing data
 * (Order/Invoice/Stock/Return, from later phases) to compute honestly, so
 * they're defined here now but unused until then rather than faked.
 */
enum ReconciliationStatus: string
{
    case Matched = 'matched';
    case SfaOnly = 'sfa_only';
    case TallyOnly = 'tally_only';
    case AmountMismatch = 'amount_mismatch';
    case QuantityMismatch = 'quantity_mismatch';
    case CustomerMismatch = 'customer_mismatch';
    case NotSynced = 'not_synced';

    public function label(): string
    {
        return match ($this) {
            self::Matched => 'Matched',
            self::SfaOnly => 'SFA Only',
            self::TallyOnly => 'Tally Only',
            self::AmountMismatch => 'Amount Mismatch',
            self::QuantityMismatch => 'Quantity Mismatch',
            self::CustomerMismatch => 'Customer Mismatch',
            self::NotSynced => 'Not Synced',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Matched => 'success',
            self::NotSynced, self::SfaOnly, self::TallyOnly => 'secondary',
            default => 'danger',
        };
    }
}
