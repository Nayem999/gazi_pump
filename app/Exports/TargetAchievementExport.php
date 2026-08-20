<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetAchievementExport implements FromCollection, WithHeadings, WithMapping
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
            'Executive', 'Period', 'Sales Target', 'Sales Achieved', 'Sales %',
            'Collection Target', 'Collection Achieved', 'Collection %',
            'Quantity Target', 'Quantity Achieved', 'Quantity %', 'Overall %', 'Grade',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->year.'-'.str_pad((string) $row->month, 2, '0', STR_PAD_LEFT),
            (string) $row->sales_target,
            (string) $row->sales_achieved,
            (string) $row->sales_pct,
            (string) $row->collection_target,
            (string) $row->collection_achieved,
            (string) $row->collection_pct,
            (string) $row->quantity_target,
            (string) $row->quantity_achieved,
            (string) $row->quantity_pct,
            (string) $row->overall_pct,
            $row->grade?->value,
        ];
    }
}
