<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('attendance')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('attendances', 'date')
                    ->where('user_id', $this->route('attendance')->user_id)
                    ->ignore($this->route('attendance')->id),
            ],
            'status' => ['required', new Enum(AttendanceStatus::class)],
            'check_in_at' => ['nullable', 'date'],
            'check_out_at' => ['nullable', 'date', 'after:check_in_at'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
