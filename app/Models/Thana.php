<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ThanaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thana extends BaseModel
{
    /** @use HasFactory<ThanaFactory> */
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'name_bn',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function territories(): HasMany
    {
        return $this->hasMany(Territory::class);
    }

    public function dealers(): HasMany
    {
        return $this->hasMany(Dealer::class);
    }
}
