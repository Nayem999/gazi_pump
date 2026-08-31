<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Self-service daily achievement entry: the Sales Executive is always the
 * authenticated user, never chosen client-side. Mirrors the mode split of
 * the admin-panel form — a single overall figure, or a product-wise
 * breakdown — matching whatever shape that month's Target was set in.
 */
class StoreAchievementEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.achievements.add') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mode' => $this->input('mode', $this->filled('items') ? 'product_wise' : 'single'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entry_date' => ['nullable', 'date'],
            'mode' => ['required', Rule::in(['single', 'product_wise'])],
            'order_value_achieved' => ['required_if:mode,single', 'nullable', 'numeric', 'min:0'],
            'collection_achieved' => ['required_if:mode,single', 'nullable', 'numeric', 'min:0'],
            'quantity_achieved' => ['required_if:mode,single', 'nullable', 'integer', 'min:0'],
            'items' => ['required_if:mode,product_wise', 'nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'distinct', 'integer', Rule::exists('products', 'id')],
            'items.*.order_achieved' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.collection_achieved' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.quantity_achieved' => ['required_with:items', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Same server-side backstop as StoreOrderRequest: the mobile app is
     * expected to only ever offer products from GET /products, already
     * scoped to the executive's own sales team.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('items', []) as $index => $item) {
                $productId = $item['product_id'] ?? null;
                if ($productId && ! Product::query()->visibleTo($this->user())->whereKey($productId)->exists()) {
                    $validator->errors()->add("items.{$index}.product_id", 'This product is outside your sales team.');
                }
            }
        });
    }
}
