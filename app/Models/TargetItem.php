<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TargetItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single product's slice of a product-wise Target — wholly owned by its
 * Target (mirrors OrderItem), so no soft-delete/audit of its own.
 */
class TargetItem extends Model
{
    /** @use HasFactory<TargetItemFactory> */
    use HasFactory;

    protected $fillable = [
        'target_id',
        'product_id',
        'order_target',
        'collection_target',
        'quantity_target',
    ];

    protected function casts(): array
    {
        return [
            'order_target' => 'decimal:2',
            'collection_target' => 'decimal:2',
            'quantity_target' => 'integer',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
