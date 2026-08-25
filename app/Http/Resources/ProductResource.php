<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => (float) $this->price,
            'description' => $this->description,
            'image_url' => $this->imageUrl(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null),
            'sales_team' => $this->whenLoaded('salesTeam', fn () => $this->salesTeam ? [
                'id' => $this->salesTeam->id,
                'name' => $this->salesTeam->name,
            ] : null),
            'status' => (bool) $this->status,
        ];
    }
}
