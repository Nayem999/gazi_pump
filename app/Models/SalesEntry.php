<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SalesEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesEntry extends BaseModel
{
    /** @use HasFactory<SalesEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'sale_date',
        'total_amount',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date:Y-m-d',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesEntryItem::class);
    }
}
