<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SalesEntryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single product line within a SalesEntry. Wholly owned by its parent —
 * no soft deletes or audit columns of its own; the sale as a whole is the
 * unit of audit/trash, not each line.
 */
class SalesEntryItem extends Model
{
    /** @use HasFactory<SalesEntryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sales_entry_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function salesEntry(): BelongsTo
    {
        return $this->belongsTo(SalesEntry::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
