<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SalesEntry;
use App\Models\User;

class SalesEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales-entries.view');
    }

    public function view(User $user, SalesEntry $salesEntry): bool
    {
        return $user->can('sales-entries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sales-entries.add');
    }

    public function update(User $user, SalesEntry $salesEntry): bool
    {
        return $user->can('sales-entries.edit');
    }

    public function delete(User $user, SalesEntry $salesEntry): bool
    {
        return $user->can('sales-entries.delete');
    }

    public function restore(User $user, SalesEntry $salesEntry): bool
    {
        return $user->can('sales-entries.restore');
    }

    public function forceDelete(User $user, SalesEntry $salesEntry): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('sales-entries.export');
    }

    public function import(User $user): bool
    {
        return $user->can('sales-entries.import');
    }

    public function print(User $user): bool
    {
        return $user->can('sales-entries.print');
    }
}
