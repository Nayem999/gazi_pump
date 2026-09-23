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
        return [
            'Executive', 'Territory', 'Planned', 'Completed', 'Missed', 'Completion Rate %',
            'Total Visits', 'GPS Verified', 'GPS Verified Rate %',
            'Orders', 'Order Value', 'Avg Order Value',
            'Productive Dealers', 'Visited Dealers', 'Visited Dealers Who Ordered', 'Strike Rate %',
        ];
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
            $row->order_count,
            // Raw numbers rather than number_format() strings, so the
            // spreadsheet can still sum and average these columns.
            $row->order_value,
            $row->avg_order_value,
            $row->productive_dealers,
            // Both halves of the strike rate exported explicitly, so a
            // reader can check the percentage rather than trust it.
            $row->visited_dealers,
            $row->converted_dealers,
            (string) $row->strike_rate,
        ];
    }
}
