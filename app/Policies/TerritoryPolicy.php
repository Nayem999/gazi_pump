<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Territory;
use App\Models\User;

class TerritoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('territories.view');
    }

    public function view(User $user, Territory $territory): bool
    {
        return $user->can('territories.view') && Territory::query()->visibleTo($user)->whereKey($territory->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('territories.add');
    }

    public function update(User $user, Territory $territory): bool
    {
        return $user->can('territories.edit');
    }

    public function delete(User $user, Territory $territory): bool
    {
        if ($territory->users()->exists()) {
            return false;
        }

        return $user->can('territories.delete');
    }

    public function restore(User $user, Territory $territory): bool
    {
        return $user->can('territories.restore');
    }

    public function forceDelete(User $user, Territory $territory): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('territories.export');
    }

    public function import(User $user): bool
    {
        return $user->can('territories.import');
    }

    public function print(User $user): bool
    {
        return $user->can('territories.print');
    }
}
