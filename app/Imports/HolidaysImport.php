<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HolidaysImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new Holiday([
            'name' => $row['name'],
            'date' => $row['date'],
            'description' => $row['description'] ?? null,
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
            'date' => ['required', 'date', 'unique:holidays,date'],
        ];
    }
}
