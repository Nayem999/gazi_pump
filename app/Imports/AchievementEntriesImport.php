<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\AchievementEntry;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk-records daily achievements — each imported row lands as Pending, same
 * as one entered through the form, so a manager still reviews it before it
 * counts toward a Target.
 */
class AchievementEntriesImport implements ToModel, WithHeadingRow, WithValidation
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

        return AchievementEntry::updateOrCreate(
            ['user_id' => $userId, 'entry_date' => $row['entry_date']],
            [
                'order_value_achieved' => $row['order_value_achieved'],
                'collection_achieved' => $row['collection_achieved'],
                'quantity_achieved' => $row['quantity_achieved'],
                'notes' => $row['notes'] ?? null,
                'created_by' => $currentUser?->getAuthIdentifier(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:users,employee_id'],
            'entry_date' => ['required', 'date'],
            'order_value_achieved' => ['required', 'numeric', 'min:0'],
            'collection_achieved' => ['required', 'numeric', 'min:0'],
            'quantity_achieved' => ['required', 'integer', 'min:0'],
        ];
    }
}
