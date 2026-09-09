<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\LeaveRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveRequestsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, LeaveRequest>  $leaveRequests
     */
    public function __construct(private readonly Collection $leaveRequests) {}

    public function collection(): Collection
    {
        return $this->leaveRequests;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee', 'Employee ID', 'Leave Type', 'From', 'To', 'Days', 'Half Day', 'Status', 'Decided By', 'Decided At', 'Reason', 'Decision Remarks'];
    }

    /**
     * @param  LeaveRequest  $row
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->employee_id,
            $row->leaveType?->name,
            $row->from_date?->format('d M Y'),
            $row->to_date?->format('d M Y'),
            (string) $row->days,
            $row->is_half_day ? 'Yes' : 'No',
            $row->status->label(),
            $row->approver?->name,
            $row->approved_at?->format('d M Y, h:i A'),
            $row->reason,
            $row->decision_remarks,
        ];
    }
}
