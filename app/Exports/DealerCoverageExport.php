<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DealerCoverageExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, object>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Territory', 'Total Dealers', 'Visited', 'Not Visited', 'Coverage Rate %'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->territory?->name,
            (string) $row->total_dealers,
            (string) $row->visited_dealers,
            (string) $row->not_visited_dealers,
            (string) $row->coverage_rate,
        ];
    }
}
