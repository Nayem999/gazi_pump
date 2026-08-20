<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, User>  $users
     */
    public function __construct(private readonly Collection $users) {}

    public function collection(): Collection
    {
        return $this->users;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee ID', 'Name', 'Email', 'Phone', 'Designation', 'Manager', 'Status', 'Roles'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($user): array
    {
        return [
            $user->employee_id,
            $user->name,
            $user->email,
            $user->phone,
            $user->designation,
            $user->manager?->name,
            $user->status ? 'Active' : 'Inactive',
            $user->roles->pluck('name')->implode(', '),
        ];
    }
}
