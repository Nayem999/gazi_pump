<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitComplianceExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Executive', 'Territory', 'Planned', 'Completed', 'Missed', 'Completion Rate %', 'Total Visits', 'GPS Verified', 'GPS Verified Rate %'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->territory_names,
            $row->planned_count,
            $row->completed_count,
            $row->missed_count,
            (string) $row->completion_rate,
            $row->total_visits,
            $row->gps_verified_count,
            (string) $row->gps_verified_rate,
        ];
    }
}
