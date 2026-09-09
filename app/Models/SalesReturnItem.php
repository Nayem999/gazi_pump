<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SalesReturnItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Wholly owned by its SalesReturn, same shape as OrderItem — no soft
 * delete/audit of its own. received_qty is null until the depot-receiving
 * step confirms it, and can be less than requested_qty.
 */
class SalesReturnItem extends Model
{
    /** @use HasFactory<SalesReturnItemFactory> */
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'order_item_id',
        'product_id',
        'requested_qty',
        'received_qty',
        'unit_price',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'requested_qty' => 'decimal:2',
            'received_qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
