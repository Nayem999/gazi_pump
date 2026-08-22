<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Division;
use Illuminate\Foundation\Http\FormRequest;

class StoreDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Division::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:divisions,name'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }
}
