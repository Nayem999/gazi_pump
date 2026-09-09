<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Driver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DriversExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Driver>  $drivers
     */
    public function __construct(private readonly Collection $drivers) {}

    public function collection(): Collection
    {
        return $this->drivers;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'License Number', 'Phone', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($driver): array
    {
        return [
            $driver->name,
            $driver->license_number,
            $driver->phone,
            $driver->status ? 'Active' : 'Inactive',
        ];
    }
}
