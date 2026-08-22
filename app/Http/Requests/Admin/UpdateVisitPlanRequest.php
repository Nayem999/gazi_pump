<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\VisitPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateVisitPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('visit_plan')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'planned_date' => ['required', 'date'],
            'status' => ['required', new Enum(VisitPlanStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
