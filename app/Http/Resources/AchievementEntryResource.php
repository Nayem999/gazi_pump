<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AchievementEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AchievementEntry
 */
class AchievementEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entry_date' => $this->entry_date->toDateString(),
            'order_value_achieved' => (float) $this->order_value_achieved,
            'collection_achieved' => (float) $this->collection_achieved,
            'quantity_achieved' => $this->quantity_achieved,
            'is_product_wise' => $this->isProductWise(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->relationLoaded('product') && $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                ] : null,
                'order_achieved' => (float) $item->order_achieved,
                'collection_achieved' => (float) $item->collection_achieved,
                'quantity_achieved' => $item->quantity_achieved,
            ])),
            'notes' => $this->notes,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
