<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Depot;
use App\Models\User;

class DepotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('depots.view');
    }

    public function view(User $user, Depot $depot): bool
    {
        return $user->can('depots.view');
    }

    public function create(User $user): bool
    {
        return $user->can('depots.add');
    }

    public function update(User $user, Depot $depot): bool
    {
        return $user->can('depots.edit');
    }

    public function delete(User $user, Depot $depot): bool
    {
        return $user->can('depots.delete');
    }

    public function restore(User $user, Depot $depot): bool
    {
        return $user->can('depots.restore');
    }

    public function forceDelete(User $user, Depot $depot): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('depots.export');
    }

    public function import(User $user): bool
    {
        return $user->can('depots.import');
    }

    public function print(User $user): bool
    {
        return $user->can('depots.print');
    }
}
