<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends BaseModel
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_code',
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

    public function salesEntries(): HasMany
    {
        return $this->hasMany(SalesEntry::class);
    }

    public function hasGps(): bool
    {
        return $this->gps_lat !== null && $this->gps_lng !== null;
    }
}
