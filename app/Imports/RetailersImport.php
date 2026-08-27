<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Dealer;
use App\Models\Retailer;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RetailersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new Retailer([
            'dealer_id' => Dealer::where('dealer_code', $row['dealer_code'])->value('id'),
            'name' => $row['name'],
            'phone' => $row['phone'],
            'email' => $row['email'] ?? null,
            'shipping_address' => $row['shipping_address'] ?? null,
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_code' => ['required', 'string', 'exists:dealers,dealer_code'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ];
    }
}
