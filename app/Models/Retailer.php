<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RetailerFactory;
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
}
