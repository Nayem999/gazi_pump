<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehiclesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Vehicle>  $vehicles
     */
    public function __construct(private readonly Collection $vehicles) {}

    public function collection(): Collection
    {
        return $this->vehicles;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Registration Number', 'Type', 'Capacity', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($vehicle): array
    {
        return [
            $vehicle->registration_number,
            $vehicle->type,
            $vehicle->capacity,
            $vehicle->status ? 'Active' : 'Inactive',
        ];
    }
}
