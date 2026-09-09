<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('leave_balance')) ?? false;
    }

    /**
     * Only the numbers are editable. Who and which year an entitlement is
     * for is its identity - moving it would silently re-grant one person's
     * days to another. Delete it and add the right one instead, which
     * leaves both acts on the audit trail.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entitled_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'carried_forward_days' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
