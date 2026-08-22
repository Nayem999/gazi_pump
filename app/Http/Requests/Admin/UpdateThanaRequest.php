<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('thana')) ?? false;
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
                Rule::unique('thanas', 'name')
                    ->where(fn ($query) => $query->where('district_id', $this->input('district_id')))
                    ->ignore($this->route('thana')->id),
            ],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }
}
