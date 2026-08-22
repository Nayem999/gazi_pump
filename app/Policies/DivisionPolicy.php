<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Division;
use App\Models\User;

class DivisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('divisions.view');
    }

    public function view(User $user, Division $division): bool
    {
        return $user->can('divisions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('divisions.add');
    }

    public function update(User $user, Division $division): bool
    {
        return $user->can('divisions.edit');
    }

    public function delete(User $user, Division $division): bool
    {
        if ($division->districts()->exists() || $division->territories()->exists() || $division->dealers()->exists()) {
            return false;
        }

        return $user->can('divisions.delete');
    }

    public function restore(User $user, Division $division): bool
    {
        return $user->can('divisions.restore');
    }

    public function forceDelete(User $user, Division $division): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('divisions.export');
    }

    public function import(User $user): bool
    {
        return $user->can('divisions.import');
    }

    public function print(User $user): bool
    {
        return $user->can('divisions.print');
    }
}
