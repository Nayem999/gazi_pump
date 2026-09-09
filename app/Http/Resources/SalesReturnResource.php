<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SalesReturn
 */
class SalesReturnResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_reference' => $this->external_reference,
            'order_id' => $this->order_id,
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'sync_status' => $this->sync_status->value,
            'sync_status_label' => $this->sync_status->label(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->relationLoaded('product') && $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                ] : null,
                'requested_qty' => (float) $item->requested_qty,
                'received_qty' => $item->received_qty !== null ? (float) $item->received_qty : null,
                'unit_price' => (float) $item->unit_price,
                'total_amount' => (float) $item->total_amount,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
