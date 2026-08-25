<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Holiday;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HolidaysExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Holiday>  $holidays
     */
    public function __construct(private readonly Collection $holidays) {}

    public function collection(): Collection
    {
        return $this->holidays;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Date', 'Name', 'Description', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($holiday): array
    {
        return [
            $holiday->date->format('Y-m-d'),
            $holiday->name,
            $holiday->description,
            $holiday->status ? 'Active' : 'Inactive',
        ];
    }
}
