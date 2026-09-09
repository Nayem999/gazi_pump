<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\LeaveBalance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveBalancesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, LeaveBalance>  $leaveBalances
     */
    public function __construct(private readonly Collection $leaveBalances) {}

    public function collection(): Collection
    {
        return $this->leaveBalances;
    }

    /**
     * Entitlement only - "taken" and "remaining" are not exported here
     * because they are derived from approved requests, and the Leave
     * Requests export is where that data belongs.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee', 'Employee ID', 'Year', 'Leave Type', 'Entitled Days', 'Carried Forward', 'Total', 'Remarks'];
    }

    /**
     * @param  LeaveBalance  $row
     * @return array<int, string|int|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->employee_id,
            $row->year,
            $row->leaveType?->name,
            (string) $row->entitled_days,
            (string) $row->carried_forward_days,
            (string) $row->totalEntitlement(),
            $row->remarks,
        ];
    }
}
