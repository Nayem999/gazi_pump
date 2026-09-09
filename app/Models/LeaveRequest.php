<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Models\Concerns\HasVisibilityScope;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One request for time off, from submission to a manager's decision.
 *
 * `days` is the working-day count as calculated at submission - weekends
 * and holidays already removed. It is stored rather than recomputed
 * because the holiday calendar can be edited later, and an approved
 * request has to keep the number of days it was actually approved for.
 */
class LeaveRequest extends BaseModel
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    use HasVisibilityScope;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'days',
        'is_half_day',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'decision_remarks',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date:Y-m-d',
            'to_date' => 'date:Y-m-d',
            'days' => 'decimal:1',
            'is_half_day' => 'boolean',
            'status' => LeaveStatus::class,
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Only approved leave consumes a balance or blocks the calendar. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Approved);
    }

    /**
     * Requests that still hold the dates - approved ones, plus pending
     * ones so a person cannot stack two overlapping requests for the same
     * days while the first is still being decided.
     */
    public function scopeOccupying(Builder $query): Builder
    {
        return $query->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved]);
    }

    /** Requests touching any part of the given range. */
    public function scopeOverlapping(Builder $query, string $from, string $to): Builder
    {
        return $query->where('from_date', '<=', $to)->where('to_date', '>=', $from);
    }

    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveStatus::Approved;
    }

    /** The calendar year the balance is drawn from. */
    public function year(): int
    {
        return (int) $this->from_date->format('Y');
    }
}
