<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', District::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('districts', 'name')->where(fn ($query) => $query->where('division_id', $this->input('division_id'))),
            ],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }
}
