<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GpsReportExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Executive', 'Territory', 'Ping Count', 'Avg Accuracy (m)', 'Avg Battery %', 'Last Seen'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->territory?->name,
            (string) $row->ping_count,
            $row->avg_accuracy !== null ? (string) $row->avg_accuracy : null,
            $row->avg_battery_level !== null ? (string) $row->avg_battery_level : null,
            $row->last_seen_at?->format('Y-m-d H:i'),
        ];
    }
}
