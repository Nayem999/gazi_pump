<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SalesTeam;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesTeamsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, SalesTeam>  $salesTeams
     */
    public function __construct(private readonly Collection $salesTeams) {}

    public function collection(): Collection
    {
        return $this->salesTeams;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Description', 'Members', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($salesTeam): array
    {
        return [
            $salesTeam->code,
            $salesTeam->name,
            $salesTeam->description,
            $salesTeam->users_count ?? $salesTeam->users()->count(),
            $salesTeam->status ? 'Active' : 'Inactive',
        ];
    }
}
