<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * For backfilling a visit an admin already knows happened (e.g. from a
 * paper log) — no photo upload here, unlike the mobile check-in/out API,
 * since there's no photo to attach for a record entered after the fact.
 */
class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Visit::class) ?? false;
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
