<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;

class SubmitLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LeaveRequest::class) ?? false;
    }

    /**
     * Shape only. Whether the leave is legitimate - date ordering,
     * overlaps, half-day rules, whether the range contains any working
     * days - is decided by LeaveRequestService::submit(), which the admin
     * web form goes through too. Duplicating those rules here is how the
     * two surfaces would drift apart.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'is_half_day' => ['boolean'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
