<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Target
 */
class TargetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'year' => $this->year,
            'period_label' => $this->periodLabel(),
            'sales_value_target' => (float) $this->sales_value_target,
            'collection_target' => (float) $this->collection_target,
            'quantity_target' => $this->quantity_target,
            'notes' => $this->notes,
            'achievement' => $this->whenLoaded('achievement', fn () => $this->achievement ? [
                'sales_achieved' => (float) $this->achievement->sales_achieved,
                'collection_achieved' => (float) $this->achievement->collection_achieved,
                'quantity_achieved' => $this->achievement->quantity_achieved,
                'sales_pct' => (float) $this->achievement->sales_pct,
                'collection_pct' => (float) $this->achievement->collection_pct,
                'quantity_pct' => (float) $this->achievement->quantity_pct,
                'overall_pct' => (float) $this->achievement->overall_pct,
                'grade' => $this->achievement->grade->value,
                'grade_label' => $this->achievement->grade->label(),
                'calculated_at' => $this->achievement->calculated_at?->toIso8601String(),
            ] : null),
        ];
    }
}
