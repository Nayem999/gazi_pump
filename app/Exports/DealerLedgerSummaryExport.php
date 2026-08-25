<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DealerLedgerSummaryExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Dealer Code', 'Dealer Name', 'Territory', 'Total Ordered', 'Total Collected', 'Due Amount'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->dealer->dealer_code,
            $row->dealer->name,
            $row->dealer->territory?->name,
            number_format($row->total_ordered, 2),
            number_format($row->total_collected, 2),
            number_format($row->due_amount, 2),
        ];
    }
}
