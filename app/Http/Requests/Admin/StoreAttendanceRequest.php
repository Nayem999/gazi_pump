<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Attendance::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'date' => [
                'required',
                'date',
                Rule::unique('attendances', 'date')->where('user_id', $this->input('user_id')),
            ],
            'status' => ['required', new Enum(AttendanceStatus::class)],
            'check_in_at' => ['nullable', 'date'],
            'check_out_at' => ['nullable', 'date', 'after:check_in_at'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
