<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Thana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Thana::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('thanas', 'name')->where(fn ($query) => $query->where('district_id', $this->input('district_id'))),
            ],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }
}
