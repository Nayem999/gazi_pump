<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Dealer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DealersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Dealer>  $dealers
     */
    public function __construct(private readonly Collection $dealers) {}

    public function collection(): Collection
    {
        return $this->dealers;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Phone', 'Email', 'Address', 'Division', 'District', 'Thana', 'Territory', 'GPS Lat', 'GPS Lng', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($dealer): array
    {
        return [
            $dealer->dealer_code,
            $dealer->name,
            $dealer->phone,
            $dealer->email,
            $dealer->address,
            $dealer->division?->name,
            $dealer->district?->name,
            $dealer->thana?->name,
            $dealer->territory?->name,
            $dealer->gps_lat,
            $dealer->gps_lng,
            $dealer->status ? 'Active' : 'Inactive',
        ];
    }
}
