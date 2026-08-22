<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.visits.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'visit_plan_id' => ['nullable', 'integer', Rule::exists('visit_plans', 'id')],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ];
    }
}
