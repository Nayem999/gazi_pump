<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendancesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Attendance>  $attendances
     */
    public function __construct(private readonly Collection $attendances) {}

    public function collection(): Collection
    {
        return $this->attendances;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee ID', 'Name', 'Date', 'Check In', 'Check Out', 'Status', 'Late (min)', 'Remarks'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($attendance): array
    {
        return [
            $attendance->user?->employee_id,
            $attendance->user?->name,
            $attendance->date->toDateString(),
            $attendance->check_in_at?->format('H:i'),
            $attendance->check_out_at?->format('H:i'),
            $attendance->status->label(),
            $attendance->late_minutes,
            $attendance->remarks,
        ];
    }
}
