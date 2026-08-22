<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Thana;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ThanasExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Thana>  $thanas
     */
    public function __construct(private readonly Collection $thanas) {}

    public function collection(): Collection
    {
        return $this->thanas;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Name (Bangla)', 'District', 'Division', 'Territories', 'Dealers', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($thana): array
    {
        return [
            $thana->name,
            $thana->name_bn,
            $thana->district?->name,
            $thana->district?->division?->name,
            $thana->territories_count ?? $thana->territories()->count(),
            $thana->dealers_count ?? $thana->dealers()->count(),
            $thana->status ? 'Active' : 'Inactive',
        ];
    }
}
