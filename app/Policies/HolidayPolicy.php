<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('holidays.view');
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->can('holidays.view');
    }

    public function create(User $user): bool
    {
        return $user->can('holidays.add');
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->can('holidays.edit');
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->can('holidays.delete');
    }

    public function restore(User $user, Holiday $holiday): bool
    {
        return $user->can('holidays.restore');
    }

    public function forceDelete(User $user, Holiday $holiday): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('holidays.export');
    }

    public function import(User $user): bool
    {
        return $user->can('holidays.import');
    }

    public function print(User $user): bool
    {
        return $user->can('holidays.print');
    }
}
