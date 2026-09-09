<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LedgerEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Tally voucher affecting a dealer's ledger (Sales, Receipt, Credit
 * Note, Debit Note, Journal — whatever voucher_type Tally itself uses).
 * Pulled and upserted by tally_guid (see TallyLedgerSyncService) — Tally is
 * the sole source of truth, so this is a synced cache, not a business
 * record, same reasoning as ProductStock.
 */
class LedgerEntry extends Model
{
    /** @use HasFactory<LedgerEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'tally_guid',
        'voucher_date',
        'voucher_type',
        'voucher_number',
        'debit_amount',
        'credit_amount',
        'narration',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'voucher_date' => 'date:Y-m-d',
            'debit_amount' => 'decimal:2',
            'credit_amount' => 'decimal:2',
            'synced_at' => 'datetime',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
