<?php

declare(strict_types=1);

namespace App\Imports;

use App\Actions\CalculateAchievementAction;
use App\Models\Target;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk-assigns monthly targets. Each imported target has its achievement
 * calculated immediately (same as a manually created one), so the import
 * doesn't leave targets looking un-tracked until someone happens to hit
 * "Recalculate".
 */
class TargetsImport implements ToModel, WithHeadingRow, WithValidation
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

        $target = Target::updateOrCreate(
            ['user_id' => $userId, 'month' => (int) $row['month'], 'year' => (int) $row['year']],
            [
                'order_value_target' => $row['order_value_target'],
                'collection_target' => $row['collection_target'],
                'quantity_target' => $row['quantity_target'],
                'notes' => $row['notes'] ?? null,
                'created_by' => $currentUser?->getAuthIdentifier(),
            ],
        );

        app(CalculateAchievementAction::class)($target);

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:users,employee_id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'order_value_target' => ['required', 'numeric', 'min:1'],
            'collection_target' => ['required', 'numeric', 'min:1'],
            'quantity_target' => ['required', 'integer', 'min:1'],
        ];
    }
}
