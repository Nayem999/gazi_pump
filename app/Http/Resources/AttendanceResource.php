<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'late_minutes' => $this->late_minutes,
            'check_in' => $this->when($this->check_in_at !== null, fn () => [
                'at' => $this->check_in_at?->toIso8601String(),
                'lat' => $this->check_in_lat !== null ? (float) $this->check_in_lat : null,
                'lng' => $this->check_in_lng !== null ? (float) $this->check_in_lng : null,
                'photo_url' => $this->checkInPhotoUrl(),
            ]),
            'check_out' => $this->when($this->check_out_at !== null, fn () => [
                'at' => $this->check_out_at?->toIso8601String(),
                'lat' => $this->check_out_lat !== null ? (float) $this->check_out_lat : null,
                'lng' => $this->check_out_lng !== null ? (float) $this->check_out_lng : null,
                'photo_url' => $this->checkOutPhotoUrl(),
            ]),
            'remarks' => $this->remarks,
        ];
    }
}
