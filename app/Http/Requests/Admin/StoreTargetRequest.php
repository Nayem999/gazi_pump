<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Target;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Target::class) ?? false;
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
                Rule::unique('targets', 'user_id')->where(
                    fn ($query) => $query->where('month', $this->input('month'))->where('year', $this->input('year'))
                ),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'order_value_target' => ['required', 'numeric', 'min:1'],
            'collection_target' => ['required', 'numeric', 'min:1'],
            'quantity_target' => ['required', 'integer', 'min:1'],
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
        ];
    }
}
