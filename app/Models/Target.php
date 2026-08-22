<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TargetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Target extends BaseModel
{
    /** @use HasFactory<TargetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'order_value_target',
        'collection_target',
        'quantity_target',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'order_value_target' => 'decimal:2',
            'collection_target' => 'decimal:2',
            'quantity_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): HasOne
    {
        return $this->hasOne(Achievement::class);
    }

    public function periodLabel(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}
