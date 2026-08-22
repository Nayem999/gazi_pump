<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DistrictsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        $divisionId = Division::where('name', $row['division'])->value('id');

        return new District([
            'division_id' => $divisionId,
            'name' => $row['name'],
            'name_bn' => $row['name_bn'] ?? null,
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'division' => ['required', 'string', 'exists:divisions,name'],
            'name' => ['required', 'string'],
            'name_bn' => ['nullable', 'string'],
        ];
    }
}
