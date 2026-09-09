<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DriversImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new Driver([
            'name' => $row['name'],
            'license_number' => $row['license_number'],
            'phone' => $row['phone'] ?? null,
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
            'license_number' => ['required', 'string', 'unique:drivers,license_number'],
        ];
    }
}
