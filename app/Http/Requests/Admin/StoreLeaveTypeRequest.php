<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LeaveType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:leave_types,code'],
            'description' => ['nullable', 'string'],
            // Capped rather than left open: a quota in the hundreds is a
            // typo, and it would flow straight into everyone's entitlement.
            'annual_quota' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['boolean'],
            'status' => ['boolean'],
        ];
    }
}
