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
        return ['Executive', 'Territory', 'Present', 'Late', 'Half Day', 'Absent', 'On Leave', 'Late Minutes', 'Total Days', 'Working Days Judged', 'Attendance Rate %'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->territory_names,
            $row->present_count,
            $row->late_count,
            $row->half_day_count,
            $row->absent_count,
            $row->leave_count,
            $row->total_late_minutes,
            $row->total_days,
            // Named so the rate is checkable: it divides by working days,
            // with approved leave excluded rather than counted against it.
            $row->judged_days,
            (string) $row->attendance_rate,
        ];
    }
}
