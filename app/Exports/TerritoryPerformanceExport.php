<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TerritoryPerformanceExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Territory', 'Executives', 'Total Order Value', 'Total Collection Amount', 'Total Visits', 'GPS Verified Rate %'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->territory?->name,
            $row->executive_count,
            (string) $row->total_order_value,
            (string) $row->total_collection_amount,
            $row->total_visits,
            (string) $row->gps_verified_rate,
        ];
    }
}
