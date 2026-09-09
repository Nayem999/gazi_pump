<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The policy allows this only while the request is still pending -
        // editing dates under a decision already made would misrepresent
        // what was agreed.
        return $this->user()?->can('update', $this->route('leave_request')) ?? false;
    }

    /**
     * No user_id: an edit never moves a request to a different person.
     * Cancel it and file a new one instead, so the original stays on
     * record against whoever actually asked for it.
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
