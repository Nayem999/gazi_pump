<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DealerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends BaseModel
{
    /** @use HasFactory<DealerFactory> */
    use HasFactory;

    protected $fillable = [
        'dealer_code',
        'name',
        'phone',
        'email',
        'address',
        'image',
        'gps_lat',
        'gps_lng',
        'division_id',
        'district_id',
        'thana_id',
        'territory_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
        ];
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function thana(): BelongsTo
    {
        return $this->belongsTo(Thana::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function collectionEntries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class);
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class);
    }

    public function hasGps(): bool
    {
        return $this->gps_lat !== null && $this->gps_lng !== null;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Restricts to dealers in the viewer's own territories — dealers have
     * no owning executive of their own, so unlike Order/CollectionEntry
     * there's no self-only tier, just "has territories assigned" or not.
     * A viewer with no territories (Super Admin, General Manager, and any
     * Sales Executive not yet assigned one) sees every dealer unfiltered.
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        $territoryIds = $viewer->territories->pluck('id')->all();

        return $territoryIds === [] ? $query : $query->whereIn('territory_id', $territoryIds);
    }
}
