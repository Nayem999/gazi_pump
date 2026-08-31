<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Models\Concerns\HasVisibilityScope;
use Database\Factories\AchievementEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Sales Executive's daily self-reported achievement — the "version 1"
 * replacement for individually recorded Orders/CollectionEntries. Structured
 * like Target (single overall figure, or a product-wise breakdown via
 * `items`) but for one day instead of a whole month, and carries its own
 * Pending/Approved/Rejected review cycle (see App\Enums\ApprovalStatus,
 * shared with Order/CollectionEntry). Only Approved entries count toward a
 * Target's computed achievement — see CalculateAchievementAction.
 */
class AchievementEntry extends BaseModel
{
    /** @use HasFactory<AchievementEntryFactory> */
    use HasFactory;

    use HasVisibilityScope;

    protected $fillable = [
        'user_id',
        'entry_date',
        'order_value_achieved',
        'collection_achieved',
        'quantity_achieved',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date:Y-m-d',
            'order_value_achieved' => 'decimal:2',
            'collection_achieved' => 'decimal:2',
            'quantity_achieved' => 'integer',
            'status' => ApprovalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AchievementItem::class);
    }

    public function entryDateLabel(): string
    {
        return $this->entry_date->format('M d, Y');
    }

    /**
     * True when this entry was reported product-by-product rather than as
     * one overall figure — its order_value_achieved/collection_achieved/
     * quantity_achieved columns are still populated either way (auto-summed
     * from the product rows when product-wise), mirroring Target::isProductWise().
     */
    public function isProductWise(): bool
    {
        return $this->items->isNotEmpty();
    }
}
