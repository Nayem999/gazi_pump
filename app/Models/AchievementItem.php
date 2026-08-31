<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AchievementItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single product's slice of a product-wise AchievementEntry — wholly owned
 * by its entry (mirrors TargetItem), so no soft-delete/audit of its own.
 */
class AchievementItem extends Model
{
    /** @use HasFactory<AchievementItemFactory> */
    use HasFactory;

    protected $fillable = [
        'achievement_entry_id',
        'product_id',
        'order_achieved',
        'collection_achieved',
        'quantity_achieved',
    ];

    protected function casts(): array
    {
        return [
            'order_achieved' => 'decimal:2',
            'collection_achieved' => 'decimal:2',
            'quantity_achieved' => 'integer',
        ];
    }

    public function achievementEntry(): BelongsTo
    {
        return $this->belongsTo(AchievementEntry::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
