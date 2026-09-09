<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveTypesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, LeaveType>  $leaveTypes
     */
    public function __construct(private readonly Collection $leaveTypes) {}

    public function collection(): Collection
    {
        return $this->leaveTypes;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Code', 'Annual Quota (days)', 'Paid', 'Status', 'Description'];
    }

    /**
     * @param  LeaveType  $row
     * @return array<int, string|int|null>
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->code,
            $row->annual_quota,
            $row->is_paid ? 'Paid' : 'Unpaid',
            $row->status ? 'Active' : 'Inactive',
            $row->description,
        ];
    }
}
