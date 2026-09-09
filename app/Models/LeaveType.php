<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LeaveTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A category of leave - Casual, Sick, Annual, Unpaid and so on. Master
 * data owned by HR; the yearly quota here is only the default used when
 * setting up someone's entitlement, not the entitlement itself.
 */
class LeaveType extends BaseModel
{
    /** @use HasFactory<LeaveTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'annual_quota',
        'is_paid',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'annual_quota' => 'integer',
            'is_paid' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
