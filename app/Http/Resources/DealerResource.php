<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dealer
 */
class DealerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dealer_code' => $this->dealer_code,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'gps' => $this->when($this->hasGps(), fn () => [
                'lat' => (float) $this->gps_lat,
                'lng' => (float) $this->gps_lng,
            ]),
            'territory' => $this->whenLoaded('territory', fn () => $this->territory ? [
                'id' => $this->territory->id,
                'name' => $this->territory->name,
            ] : null),
            'status' => (bool) $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
