<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk backfill/correction import: HR uploads employee_id + date + status
 * rows to record attendance for days the mobile check-in wasn't used
 * (e.g. office duty, approved leave).
 */
class AttendancesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $userId = User::where('employee_id', $row['employee_id'])->value('id');

        if (! $userId) {
            return null;
        }

        return new Attendance([
            'user_id' => $userId,
            'date' => $row['date'],
            'check_in_at' => ! empty($row['check_in_time']) ? "{$row['date']} {$row['check_in_time']}" : null,
            'check_out_at' => ! empty($row['check_out_time']) ? "{$row['date']} {$row['check_out_time']}" : null,
            'status' => $row['status'],
            'remarks' => $row['remarks'] ?? null,
            'created_by' => $currentUser?->getAuthIdentifier(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:users,employee_id'],
            'date' => ['required', 'date'],
            'status' => ['required', new Enum(AttendanceStatus::class)],
            'check_in_time' => ['nullable'],
            'check_out_time' => ['nullable'],
        ];
    }
}
