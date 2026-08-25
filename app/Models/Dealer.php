<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
use Database\Factories\DealerFactory;
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
        'type',
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
            'type' => CustomerType::class,
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

    public function hasGps(): bool
    {
        return $this->gps_lat !== null && $this->gps_lng !== null;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
