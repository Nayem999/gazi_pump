<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VehiclesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new Vehicle([
            'registration_number' => $row['registration_number'],
            'type' => $row['type'] ?? null,
            'capacity' => $row['capacity'] ?? null,
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'unique:vehicles,registration_number'],
        ];
    }
}
