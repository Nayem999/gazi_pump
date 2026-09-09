<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SalesReturnStatus;
use App\Enums\TallyRecordSyncStatus;
use Database\Factories\SalesReturnFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends BaseModel
{
    /** @use HasFactory<SalesReturnFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dealer_id',
        'user_id',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'vehicle_id',
        'driver_id',
        'dispatched_at',
        'receiving_depot_id',
        'received_by',
        'received_at',
        'external_reference',
        'tally_guid',
        'tally_credit_note_number',
        'sync_status',
        'sync_error',
        'synced_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesReturnStatus::class,
            'approved_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
            'sync_status' => TallyRecordSyncStatus::class,
            'synced_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function receivingDepot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'receiving_depot_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    /**
     * Same visibility rule as Order (via its own dealer's territory) —
     * plain Sales Executive sees only returns they themselves requested,
     * everyone else is scoped by territory the same way Order is.
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
