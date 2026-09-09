<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vehicles.view');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vehicles.add');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.edit');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.delete');
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->can('vehicles.restore');
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('vehicles.export');
    }

    public function import(User $user): bool
    {
        return $user->can('vehicles.import');
    }

    public function print(User $user): bool
    {
        return $user->can('vehicles.print');
    }
}
