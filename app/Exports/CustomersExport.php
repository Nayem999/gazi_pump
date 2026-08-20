<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Customer>  $customers
     */
    public function __construct(private readonly Collection $customers) {}

    public function collection(): Collection
    {
        return $this->customers;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Type', 'Phone', 'Email', 'Address', 'Territory', 'GPS Lat', 'GPS Lng', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($customer): array
    {
        return [
            $customer->customer_code,
            $customer->name,
            $customer->type->label(),
            $customer->phone,
            $customer->email,
            $customer->address,
            $customer->territory?->name,
            $customer->gps_lat,
            $customer->gps_lng,
            $customer->status ? 'Active' : 'Inactive',
        ];
    }
}
