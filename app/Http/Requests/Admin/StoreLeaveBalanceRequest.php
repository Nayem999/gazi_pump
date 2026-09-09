<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\LeaveBalance;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LeaveBalance::class) ?? false;
    }

    /**
     * No unique rule on (user_id, leave_type_id, year), deliberately.
     * The controller routes through saveEntitlement(), which updates an
     * existing row - including a soft-deleted one, which a unique rule
     * would either reject outright (leaving no way to re-grant it) or
     * ignore, leading to a duplicate-key error instead.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            // Bounded to keep a typo out of the balance arithmetic while
            // still allowing a prior year to be corrected.
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            // Half days exist, so entitlements are allowed to as well.
            'entitled_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'carried_forward_days' => ['nullable', 'numeric', 'min:0', 'max:365'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
