<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('target')) ?? false;
    }

    /**
     * Defaults `mode` when the caller doesn't send one at all — every
     * existing call site (older tests, the mobile API, anything written
     * before product-wise targets existed) posts the three overall-target
     * fields with no `mode` field, and must keep being treated exactly as
     * "single" so those required_if rules below still fire the same way
     * they always have.
     *
     * A plain Sales Executive can only ever edit their own target — the
     * Executive field is disabled client-side (see targets/_form), but
     * that alone doesn't stop a tampered request, so it's overridden here
     * unconditionally before validation runs too.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mode' => $this->input('mode', $this->filled('product_targets') ? 'product_wise' : 'single'),
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
                Rule::unique('targets', 'user_id')
                    ->where(fn ($query) => $query->where('month', $this->input('month'))->where('year', $this->input('year')))
                    ->ignore($this->route('target')),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'mode' => ['required', Rule::in(['single', 'product_wise'])],
            'order_value_target' => ['required_if:mode,single', 'nullable', 'numeric', 'min:1'],
            'collection_target' => ['required_if:mode,single', 'nullable', 'numeric', 'min:1'],
            'quantity_target' => ['required_if:mode,single', 'nullable', 'integer', 'min:1'],
            'product_targets' => ['required_if:mode,product_wise', 'nullable', 'array', 'min:1'],
            'product_targets.*.product_id' => ['required_with:product_targets', 'distinct', 'integer', Rule::exists('products', 'id')],
            'product_targets.*.order_target' => ['required_with:product_targets', 'numeric', 'min:0'],
            'product_targets.*.collection_target' => ['required_with:product_targets', 'numeric', 'min:0'],
            'product_targets.*.quantity_target' => ['required_with:product_targets', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => 'This executive already has a target for the selected month and year.',
            'product_targets.*.product_id.distinct' => 'Each product can only appear once in the product-wise breakdown.',
        ];
    }
}
