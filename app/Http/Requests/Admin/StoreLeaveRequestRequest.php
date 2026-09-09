<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', LeaveRequest::class) ?? false;
    }

    /**
     * Date ordering, overlaps and the working-day count are NOT validated
     * here. They live in LeaveRequestService::submit(), so the mobile API
     * and this form cannot drift apart on the rules that decide whether
     * leave is legitimate - see that method.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Filing on someone else's behalf is a manager's action; the
            // withValidator() check below is what enforces that.
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'is_half_day' => ['boolean'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $userId = $this->input('user_id');

            if ($userId === null || (int) $userId === $this->user()?->id) {
                return;
            }

            // Without this, anyone who can create a request could file
            // leave in a colleague's name by editing one hidden field.
            if (! $this->user()?->can('leave-requests.approve')) {
                $validator->errors()->add('user_id', 'You can only submit leave for yourself.');
            }
        });
    }
}
