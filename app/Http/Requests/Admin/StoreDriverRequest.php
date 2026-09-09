<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Driver;
use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Driver::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['boolean'],
        ];
    }
}
