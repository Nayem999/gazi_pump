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
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'gps' => $this->when($this->hasGps(), fn () => [
                'lat' => (float) $this->gps_lat,
                'lng' => (float) $this->gps_lng,
            ]),
            'division' => $this->whenLoaded('division', fn () => $this->division ? [
                'id' => $this->division->id,
                'name' => $this->division->name,
            ] : null),
            'district' => $this->whenLoaded('district', fn () => $this->district ? [
                'id' => $this->district->id,
                'name' => $this->district->name,
            ] : null),
            'thana' => $this->whenLoaded('thana', fn () => $this->thana ? [
                'id' => $this->thana->id,
                'name' => $this->thana->name,
            ] : null),
            'territory' => $this->whenLoaded('territory', fn () => $this->territory ? [
                'id' => $this->territory->id,
                'name' => $this->territory->name,
            ] : null),
            'status' => (bool) $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
