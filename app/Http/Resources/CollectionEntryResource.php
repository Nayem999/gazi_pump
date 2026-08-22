<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CollectionEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionEntry
 */
class CollectionEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_date' => $this->collection_date->toDateString(),
            'dealer' => $this->whenLoaded('dealer', fn () => $this->dealer ? [
                'id' => $this->dealer->id,
                'name' => $this->dealer->name,
                'dealer_code' => $this->dealer->dealer_code,
            ] : null),
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method->value,
            'payment_method_label' => $this->payment_method->label(),
            'reference_no' => $this->reference_no,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
