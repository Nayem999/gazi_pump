<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Visit
 */
class VisitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_plan_id' => $this->visit_plan_id,
            'dealer' => $this->whenLoaded('dealer', fn () => $this->dealer ? [
                'id' => $this->dealer->id,
                'name' => $this->dealer->name,
                'dealer_code' => $this->dealer->dealer_code,
            ] : null),
            'check_in' => [
                'at' => $this->check_in_at?->toIso8601String(),
                'lat' => $this->check_in_lat !== null ? (float) $this->check_in_lat : null,
                'lng' => $this->check_in_lng !== null ? (float) $this->check_in_lng : null,
                'photo_url' => $this->checkInPhotoUrl(),
            ],
            'check_out' => $this->when($this->check_out_at !== null, fn () => [
                'at' => $this->check_out_at?->toIso8601String(),
                'lat' => $this->check_out_lat !== null ? (float) $this->check_out_lat : null,
                'lng' => $this->check_out_lng !== null ? (float) $this->check_out_lng : null,
                'photo_url' => $this->checkOutPhotoUrl(),
            ]),
            'is_gps_verified' => $this->is_gps_verified,
            'distance_from_dealer_meters' => $this->distance_from_dealer_meters !== null ? (float) $this->distance_from_dealer_meters : null,
            'feedback' => $this->feedback,
        ];
    }
}
