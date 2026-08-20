<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'preferred_date' => ['required', 'date', 'after:today'],
            'address' => ['required', 'string', 'max:1000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
