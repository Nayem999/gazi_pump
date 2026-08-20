<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Territory;
use Illuminate\Foundation\Http\FormRequest;

class StoreTerritoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Territory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:territories,code'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'center_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'center_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'boundary' => ['nullable', 'json'],
            'status' => ['boolean'],
        ];
    }
}
