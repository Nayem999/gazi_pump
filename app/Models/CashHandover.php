<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashHandoverStatus;
use Database\Factories\CashHandoverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashHandover extends BaseModel
{
    /** @use HasFactory<CashHandoverFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'handover_date',
        'status',
        'confirmed_by',
        'confirmed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'handover_date' => 'date:Y-m-d',
            'status' => CashHandoverStatus::class,
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
