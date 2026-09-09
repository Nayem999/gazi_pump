<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Depot;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DepotsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Depot>  $depots
     */
    public function __construct(private readonly Collection $depots) {}

    public function collection(): Collection
    {
        return $this->depots;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Address', 'Territory', 'Tally GUID', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($depot): array
    {
        return [
            $depot->code,
            $depot->name,
            $depot->address,
            $depot->territory?->name,
            $depot->tally_guid,
            $depot->status ? 'Active' : 'Inactive',
        ];
    }
}
