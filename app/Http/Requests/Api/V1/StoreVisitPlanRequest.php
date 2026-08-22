<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Self-service daily planning: a Sales Executive plans their own visits.
 * There's no `status` input here — a fresh plan always starts "planned";
 * it only changes when the exec later checks in against it, or an admin
 * cancels it via the web dashboard.
 */
class StoreVisitPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.visit-plans.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'planned_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
