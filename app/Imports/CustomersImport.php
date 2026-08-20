<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\Territory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $territoryId = ! empty($row['territory_code'])
            ? Territory::where('code', $row['territory_code'])->value('id')
            : null;

        return new Customer([
            'customer_code' => $row['customer_code'],
            'name' => $row['name'],
            'type' => $row['type'],
            'phone' => $row['phone'],
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? null,
            'territory_id' => $territoryId,
            'status' => true,
            'created_by' => $currentUser?->getAuthIdentifier(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_code' => ['required', 'string', 'unique:customers,customer_code'],
            'name' => ['required', 'string'],
            'type' => ['required', new Enum(CustomerType::class)],
            'phone' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'territory_code' => ['nullable', 'string', 'exists:territories,code'],
        ];
    }
}
