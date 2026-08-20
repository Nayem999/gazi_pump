<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TerritoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends BaseModel
{
    /** @use HasFactory<TerritoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'manager_id',
        'center_lat',
        'center_lng',
        'boundary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'boundary' => 'array',
            'center_lat' => 'decimal:7',
            'center_lng' => 'decimal:7',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'territory_id');
    }
}
