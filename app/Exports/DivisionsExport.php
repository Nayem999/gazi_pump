<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Division;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DivisionsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Division>  $divisions
     */
    public function __construct(private readonly Collection $divisions) {}

    public function collection(): Collection
    {
        return $this->divisions;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Name (Bangla)', 'Districts', 'Territories', 'Dealers', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($division): array
    {
        return [
            $division->name,
            $division->name_bn,
            $division->districts_count ?? $division->districts()->count(),
            $division->territories_count ?? $division->territories()->count(),
            $division->dealers_count ?? $division->dealers()->count(),
            $division->status ? 'Active' : 'Inactive',
        ];
    }
}
