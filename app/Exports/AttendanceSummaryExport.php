<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceSummaryExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Executive', 'Territory', 'Present', 'Late', 'Half Day', 'Absent', 'Late Minutes', 'Total Days', 'Attendance Rate %'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->territory?->name,
            $row->present_count,
            $row->late_count,
            $row->half_day_count,
            $row->absent_count,
            $row->total_late_minutes,
            $row->total_days,
            (string) $row->attendance_rate,
        ];
    }
}
