<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LeaveBalanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person's entitlement for one leave type in one year.
 *
 * Holds entitlement only. Days used are derived from approved requests by
 * LeaveBalanceService - see the migration for why a stored counter was
 * rejected.
 */
class LeaveBalance extends BaseModel
{
    /** @use HasFactory<LeaveBalanceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'carried_forward_days',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'entitled_days' => 'decimal:1',
            'carried_forward_days' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /** Everything available this year, before anything is taken. */
    public function totalEntitlement(): float
    {
        return (float) $this->entitled_days + (float) $this->carried_forward_days;
    }
}
