<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Self-service field order entry: a Sales Executive records what they sold,
 * possibly several products in one stop. There's no `user_id` or
 * `items.*.unit_price` input here — the executive is always the
 * authenticated user, and each line's unit price is snapshotted
 * server-side from the product's current price rather than trusted from
 * the client.
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.orders.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'order_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
