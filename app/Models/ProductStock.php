<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tally owns opening/in/out/closing/available (only ever overwritten
 * wholesale by a stock-sync job — see TallyStockSyncService, Phase 3).
 * reserved_qty/allocated_qty are SFA's own operational overlay for order
 * allocation and never sync back to Tally. No soft deletes/audit of its
 * own — a stock row is a synced cache, not a business record (same
 * reasoning as Achievement).
 */
class ProductStock extends Model
{
    /** @use HasFactory<ProductStockFactory> */
    use HasFactory;

    protected $fillable = [
        'depot_id',
        'product_id',
        'opening_qty',
        'in_qty',
        'out_qty',
        'closing_qty',
        'available_qty',
        'reserved_qty',
        'allocated_qty',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_qty' => 'decimal:2',
            'in_qty' => 'decimal:2',
            'out_qty' => 'decimal:2',
            'closing_qty' => 'decimal:2',
            'available_qty' => 'decimal:2',
            'reserved_qty' => 'decimal:2',
            'allocated_qty' => 'decimal:2',
            'last_synced_at' => 'datetime',
        ];
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * What's actually left to promise a new order line — Tally's own
     * available_qty minus whatever SFA has already committed to other
     * pending order lines.
     */
    public function sellableQty(): float
    {
        return max(0.0, (float) $this->available_qty - (float) $this->reserved_qty);
    }
}
