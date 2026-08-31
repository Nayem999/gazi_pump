<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    /**
     * A plain Sales Executive can only ever record an order for themself —
     * the Executive field is disabled client-side (see orders/_form), but
     * that alone doesn't stop a tampered request, so it's overridden here
     * unconditionally before validation runs too.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()?->isSalesExecutiveOnly()) {
            $this->merge(['user_id' => $this->user()->id]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'retailer_id' => ['nullable', 'integer', Rule::exists('retailers', 'id')],
            'order_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

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
