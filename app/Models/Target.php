<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVisibilityScope;
use Database\Factories\TargetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Target extends BaseModel
{
    /** @use HasFactory<TargetFactory> */
    use HasFactory;

    use HasVisibilityScope;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'order_value_target',
        'collection_target',
        'quantity_target',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'order_value_target' => 'decimal:2',
            'collection_target' => 'decimal:2',
            'quantity_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): HasOne
    {
        return $this->hasOne(Achievement::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TargetItem::class);
    }

    public function periodLabel(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * True when this target was assigned product-by-product rather than as
     * one overall figure — its order_value_target/collection_target/
     * quantity_target columns are still populated either way (auto-summed
     * from the product rows when product-wise), so achievement calculation
     * never needs to know which mode a target was created in.
     */
    public function isProductWise(): bool
    {
        return $this->items->isNotEmpty();
    }
}
