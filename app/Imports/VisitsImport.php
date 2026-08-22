<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Dealer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk backfill of visits recorded on paper — no GPS verification is
 * computed here (there's no lat/lng in a paper log), so imported rows
 * always land with is_gps_verified left null ("unknown").
 */
class VisitsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $userId = User::where('employee_id', $row['employee_id'])->value('id');
        $dealerId = Dealer::where('dealer_code', $row['dealer_code'])->value('id');

        if (! $userId || ! $dealerId) {
            return null;
        }

        return new Visit([
            'user_id' => $userId,
            'dealer_id' => $dealerId,
            'check_in_at' => $row['check_in_at'],
            'check_out_at' => $row['check_out_at'] ?? null,
            'feedback' => $row['feedback'] ?? null,
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
            'dealer_code' => ['required', 'string', 'exists:dealers,dealer_code'],
            'check_in_at' => ['required', 'date'],
            'check_out_at' => ['nullable', 'date'],
        ];
    }
}
