<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Dealer;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'retailer_id' => ['nullable', 'integer', Rule::exists('retailers', 'id')],
            'order_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * The mobile app is expected to only ever offer dealers/products from
     * GET /dealers and /products, which are already scoped to the
     * executive's own territory/sales team — this is the server-side
     * backstop in case a tampered request skips that.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dealerId = $this->input('dealer_id');
            if ($dealerId && ! Dealer::query()->visibleTo($this->user())->whereKey($dealerId)->exists()) {
                $validator->errors()->add('dealer_id', 'This dealer is outside your assigned territories.');
            }

            foreach ((array) $this->input('items', []) as $index => $item) {
                $productId = $item['product_id'] ?? null;
                if ($productId && ! Product::query()->visibleTo($this->user())->whereKey($productId)->exists()) {
                    $validator->errors()->add("items.{$index}.product_id", 'This product is outside your sales team.');
                }
            }
        });
    }
}
