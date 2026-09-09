<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AllocationStatus;
use Database\Factories\DepotAllocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotAllocation extends BaseModel
{
    /** @use HasFactory<DepotAllocationFactory> */
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'depot_id',
        'requested_qty',
        'allocated_qty',
        'allocation_status',
        'is_alternative_depot',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_qty' => 'decimal:2',
            'allocated_qty' => 'decimal:2',
            'allocation_status' => AllocationStatus::class,
            'is_alternative_depot' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
