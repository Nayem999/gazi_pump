<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends BaseModel
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    protected $fillable = [
        'visit_plan_id',
        'user_id',
        'customer_id',
        'check_in_at',
        'check_in_lat',
        'check_in_lng',
        'check_in_photo',
        'check_out_at',
        'check_out_lat',
        'check_out_lng',
        'check_out_photo',
        'is_gps_verified',
        'distance_from_customer_meters',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_in_lat' => 'decimal:7',
            'check_in_lng' => 'decimal:7',
            'check_out_at' => 'datetime',
            'check_out_lat' => 'decimal:7',
            'check_out_lng' => 'decimal:7',
            'is_gps_verified' => 'boolean',
            'distance_from_customer_meters' => 'decimal:2',
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

    public function visitPlan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class);
    }

    public function isCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }

    public function checkInPhotoUrl(): ?string
    {
        return $this->check_in_photo ? asset('storage/'.$this->check_in_photo) : null;
    }

    public function checkOutPhotoUrl(): ?string
    {
        return $this->check_out_photo ? asset('storage/'.$this->check_out_photo) : null;
    }
}
