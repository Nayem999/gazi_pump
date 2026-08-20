<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        return new User([
            'employee_id' => $row['employee_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'designation' => $row['designation'] ?? null,
            'status' => true,
            'password' => Hash::make('password'),
            'created_by' => $currentUser?->getAuthIdentifier(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'unique:users,employee_id'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
        ];
    }

    public function onFailure(...$failures): void
    {
        // Row-level failures are collected by the ValidationException the
        // Excel facade throws; the controller surfaces them to the user.
    }
}
