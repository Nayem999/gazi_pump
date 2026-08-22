<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\VisitPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VisitPlan
 */
class VisitPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'planned_date' => $this->planned_date->toDateString(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_missed' => $this->isMissed(),
            'notes' => $this->notes,
            'dealer' => $this->whenLoaded('dealer', fn () => $this->dealer ? [
                'id' => $this->dealer->id,
                'name' => $this->dealer->name,
                'dealer_code' => $this->dealer->dealer_code,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
