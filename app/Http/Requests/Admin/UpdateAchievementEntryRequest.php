<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAchievementEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('achievement_entry')) ?? false;
    }

    /**
     * A plain Sales Executive can only ever edit their own achievement —
     * the Executive field is disabled client-side (see achievements/_form),
     * but that alone doesn't stop a tampered request, so it's overridden
     * here unconditionally before validation runs too.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mode' => $this->input('mode', $this->filled('achievement_items') ? 'product_wise' : 'single'),
        ]);

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
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('achievement_entries', 'user_id')
                    ->where(fn ($query) => $query->where('entry_date', $this->input('entry_date')))
                    ->ignore($this->route('achievement_entry')),
            ],
            'entry_date' => ['required', 'date'],
            'mode' => ['required', Rule::in(['single', 'product_wise'])],
            'order_value_achieved' => ['required_if:mode,single', 'nullable', 'numeric', 'min:0'],
            'collection_achieved' => ['required_if:mode,single', 'nullable', 'numeric', 'min:0'],
            'quantity_achieved' => ['required_if:mode,single', 'nullable', 'integer', 'min:0'],
            'achievement_items' => ['required_if:mode,product_wise', 'nullable', 'array', 'min:1'],
            'achievement_items.*.product_id' => ['required_with:achievement_items', 'distinct', 'integer', Rule::exists('products', 'id')],
            'achievement_items.*.order_achieved' => ['required_with:achievement_items', 'numeric', 'min:0'],
            'achievement_items.*.collection_achieved' => ['required_with:achievement_items', 'numeric', 'min:0'],
            'achievement_items.*.quantity_achieved' => ['required_with:achievement_items', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'This executive already has an achievement entry for the selected date.',
            'achievement_items.*.product_id.distinct' => 'Each product can only appear once in the product-wise breakdown.',
        ];
    }
}
