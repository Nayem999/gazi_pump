<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\LeaveBalance;
use Illuminate\Foundation\Http\FormRequest;

class SetUpLeaveEntitlementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bulk-seeding entitlements creates records, so it is gated on
        // create rather than view.
        return $this->user()?->can('create', LeaveBalance::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            // Omitted means every active employee. Present means only
            // those named, so a late joiner can be set up on their own
            // without re-running the whole company.
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
