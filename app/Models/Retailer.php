<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RetailerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retailer extends BaseModel
{
    /** @use HasFactory<RetailerFactory> */
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'name',
        'phone',
        'email',
        'image',
        'shipping_address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Restricts to retailers under a dealer the viewer can see — retailers
     * have no territory of their own, so this just defers to the parent
     * dealer's own scopeVisibleTo().
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        return $query->whereHas('dealer', fn ($q) => $q->visibleTo($viewer));
    }
}
