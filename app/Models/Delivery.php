<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\TallyRecordSyncStatus;
use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Delivery/Challan is created already dispatched (see
 * DeliveryService::dispatch()) and only ever transitions once more (to
 * Delivered/Cancelled) — no edit/delete UI, so no soft deletes or
 * created_by/updated_by ceremony of its own (dispatched_by + timestamps are
 * audit enough), same reasoning as Achievement.
 */
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vehicle_id',
        'driver_id',
        'delivery_date',
        'status',
        'external_reference',
        'tally_guid',
        'tally_delivery_number',
        'sync_status',
        'sync_error',
        'synced_at',
        'dispatched_by',
        'delivered_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date:Y-m-d',
            'status' => DeliveryStatus::class,
            'sync_status' => TallyRecordSyncStatus::class,
            'synced_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }
}
