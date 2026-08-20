<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('visit')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')],
            'visit_plan_id' => ['nullable', 'integer', Rule::exists('visit_plans', 'id')],
            'check_in_at' => ['required', 'date'],
            'check_out_at' => ['nullable', 'date', 'after:check_in_at'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
