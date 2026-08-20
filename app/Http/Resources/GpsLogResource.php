<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\GpsLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GpsLog
 */
class GpsLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'recorded_at' => $this->recorded_at->toIso8601String(),
            'accuracy' => $this->accuracy !== null ? (float) $this->accuracy : null,
            'speed' => $this->speed !== null ? (float) $this->speed : null,
            'battery_level' => $this->battery_level,
        ];
    }
}
