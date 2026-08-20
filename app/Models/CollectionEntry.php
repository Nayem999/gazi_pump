<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\CollectionEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionEntry extends BaseModel
{
    /** @use HasFactory<CollectionEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'collection_date',
        'amount',
        'payment_method',
        'reference_no',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
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
}
