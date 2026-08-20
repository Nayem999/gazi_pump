<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Territory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TerritoriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Territory>  $territories
     */
    public function __construct(private readonly Collection $territories) {}

    public function collection(): Collection
    {
        return $this->territories;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Manager', 'Executives', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($territory): array
    {
        return [
            $territory->code,
            $territory->name,
            $territory->manager?->name,
            $territory->users_count ?? $territory->users()->count(),
            $territory->status ? 'Active' : 'Inactive',
        ];
    }
}
