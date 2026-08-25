<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Holiday::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'unique:holidays,date'],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }
}
