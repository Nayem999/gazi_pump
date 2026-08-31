<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends BaseModel
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dealer_id',
        'retailer_id',
        'order_date',
        'total_amount',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date:Y-m-d',
            'total_amount' => 'decimal:2',
            'status' => ApprovalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Restricts to what the viewer is allowed to see: their own orders only
     * when Sales Executive is their sole role, orders for dealers in their
     * own territories when they have territories assigned, or every order
     * otherwise (Super Admin, General Manager, and Sales/Area Manager — a
     * `sales_team_id` doesn't gate this, see Concerns\HasVisibilityScope for
     * why). Same shape as CollectionEntry::scopeVisibleTo(), just via the
     * dealer's territory rather than the executive's own.
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isSalesExecutiveOnly()) {
            return $query->where('user_id', $viewer->id);
        }

        $territoryIds = $viewer->territories->pluck('id')->all();

        return $territoryIds === []
            ? $query
            : $query->whereHas('dealer', fn ($d) => $d->whereIn('territory_id', $territoryIds));
    }
}
