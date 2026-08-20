<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\VisitPlanStatus;
use App\Models\Customer;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VisitPlansImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $userId = User::where('employee_id', $row['employee_id'])->value('id');
        $customerId = Customer::where('customer_code', $row['customer_code'])->value('id');

        if (! $userId || ! $customerId) {
            return null;
        }

        return new VisitPlan([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'planned_date' => $row['planned_date'],
            'status' => $row['status'] ?? VisitPlanStatus::Planned->value,
            'notes' => $row['notes'] ?? null,
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
            'customer_code' => ['required', 'string', 'exists:customers,customer_code'],
            'planned_date' => ['required', 'date'],
            'status' => ['nullable', new Enum(VisitPlanStatus::class)],
        ];
    }
}
