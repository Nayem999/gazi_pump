<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('holiday')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', Rule::unique('holidays', 'date')->ignore($this->route('holiday')->id)],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }
}
