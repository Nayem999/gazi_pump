<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('leave_type')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('leave_types', 'code')->ignore($this->route('leave_type')),
            ],
            'description' => ['nullable', 'string'],
            // Capped rather than left open: a quota in the hundreds is a
            // typo, and it would flow straight into everyone's entitlement.
            'annual_quota' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['boolean'],
            'status' => ['boolean'],
        ];
    }
}
