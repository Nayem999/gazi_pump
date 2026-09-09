<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('drivers.view');
    }

    public function view(User $user, Driver $driver): bool
    {
        return $user->can('drivers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('drivers.add');
    }

    public function update(User $user, Driver $driver): bool
    {
        return $user->can('drivers.edit');
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $user->can('drivers.delete');
    }

    public function restore(User $user, Driver $driver): bool
    {
        return $user->can('drivers.restore');
    }

    public function forceDelete(User $user, Driver $driver): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('drivers.export');
    }

    public function import(User $user): bool
    {
        return $user->can('drivers.import');
    }

    public function print(User $user): bool
    {
        return $user->can('drivers.print');
    }
}
