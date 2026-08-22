<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionSummaryExport implements FromCollection, WithHeadings, WithMapping
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
        return ['Executive', 'Territory', 'Collections Count', 'Total Amount', 'Cash', 'Cheque', 'Bank Transfer', 'Mobile Banking'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->user?->name,
            $row->user?->territory_names,
            $row->collections_count,
            (string) $row->total_amount,
            (string) $row->cash_total,
            (string) $row->cheque_total,
            (string) $row->bank_transfer_total,
            (string) $row->mobile_banking_total,
        ];
    }
}
