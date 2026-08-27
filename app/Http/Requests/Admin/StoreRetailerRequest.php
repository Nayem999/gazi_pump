<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Retailer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRetailerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Retailer::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'shipping_address' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }
}
