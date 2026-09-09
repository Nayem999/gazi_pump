<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeaveRequest
 */
class LeaveRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_type' => [
                'id' => $this->leave_type_id,
                'name' => $this->leaveType?->name,
            ],
            'from_date' => $this->from_date->toDateString(),
            'to_date' => $this->to_date->toDateString(),
            // Working days only - weekends and holidays are already
            // excluded, so the app must not recompute this from the dates.
            'days' => (float) $this->days,
            'is_half_day' => $this->is_half_day,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            // Tells the app whether to show a Cancel button without it
            // having to re-implement the rules.
            'can_cancel' => $request->user() !== null
                && $request->user()->can('cancel', $this->resource)
                && ! ($this->isApproved() && $this->from_date->isPast())
                && $this->status->value !== 'cancelled'
                && $this->status->value !== 'rejected',
            'decided_by' => $this->approver?->name,
            'decided_at' => $this->approved_at?->toIso8601String(),
            'decision_remarks' => $this->decision_remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
