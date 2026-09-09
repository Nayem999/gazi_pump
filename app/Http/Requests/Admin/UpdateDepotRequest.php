<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('depot')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('depots', 'code')->ignore($this->route('depot'))],
            'address' => ['nullable', 'string', 'max:255'],
            'territory_id' => ['nullable', 'integer', 'exists:territories,id'],
            'status' => ['boolean'],
        ];
    }
}
