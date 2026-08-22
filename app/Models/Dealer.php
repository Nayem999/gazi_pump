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
        'gps_lat',
        'gps_lng',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasGps(): bool
    {
        return $this->gps_lat !== null && $this->gps_lng !== null;
    }
}
