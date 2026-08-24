<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\CustomerType;
use App\Models\Dealer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreDealerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Dealer::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_code' => ['required', 'string', 'max:50', 'unique:dealers,dealer_code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(CustomerType::class)],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'territory_id' => ['nullable', 'integer', Rule::exists('territories', 'id')],
            'status' => ['boolean'],
        ];
    }
}
