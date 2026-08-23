<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\VisitPlanStatus;
use App\Models\VisitPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreVisitPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', VisitPlan::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'dealer_ids' => ['required', 'array', 'min:1'],
            'dealer_ids.*' => ['integer', Rule::exists('dealers', 'id')],
            'territory_id' => ['nullable', 'integer', Rule::exists('territories', 'id')],
            'planned_date' => ['required', 'date'],
            'status' => ['required', new Enum(VisitPlanStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
