<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeaveTypesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new LeaveType([
            'name' => $row['name'],
            'code' => $row['code'],
            'description' => $row['description'] ?? null,
            'annual_quota' => (int) ($row['annual_quota'] ?? 0),
            // Default to paid when the column is absent: an unpaid type is
            // the exception, and silently importing everything as unpaid
            // would misrepresent the policy.
            'is_paid' => ! isset($row['is_paid']) || filter_var($row['is_paid'], FILTER_VALIDATE_BOOLEAN),
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'code' => ['required', 'string', 'unique:leave_types,code'],
            'annual_quota' => ['nullable', 'integer', 'min:0', 'max:365'],
        ];
    }
}
