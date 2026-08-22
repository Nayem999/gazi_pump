<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\District;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DistrictsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, District>  $districts
     */
    public function __construct(private readonly Collection $districts) {}

    public function collection(): Collection
    {
        return $this->districts;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Name (Bangla)', 'Division', 'Thanas', 'Territories', 'Dealers', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($district): array
    {
        return [
            $district->name,
            $district->name_bn,
            $district->division?->name,
            $district->thanas_count ?? $district->thanas()->count(),
            $district->territories_count ?? $district->territories()->count(),
            $district->dealers_count ?? $district->dealers()->count(),
            $district->status ? 'Active' : 'Inactive',
        ];
    }
}
