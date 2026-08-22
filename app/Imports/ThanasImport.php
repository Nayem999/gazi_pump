<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\District;
use App\Models\Thana;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ThanasImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        $districtId = District::where('name', $row['district'])->value('id');

        return new Thana([
            'district_id' => $districtId,
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
            'district' => ['required', 'string', 'exists:districts,name'],
            'name' => ['required', 'string'],
            'name_bn' => ['nullable', 'string'],
        ];
    }
}
