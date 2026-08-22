<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\District;
use App\Models\User;

class DistrictPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('districts.view');
    }

    public function view(User $user, District $district): bool
    {
        return $user->can('districts.view');
    }

    public function create(User $user): bool
    {
        return $user->can('districts.add');
    }

    public function update(User $user, District $district): bool
    {
        return $user->can('districts.edit');
    }

    public function delete(User $user, District $district): bool
    {
        if ($district->thanas()->exists() || $district->territories()->exists() || $district->dealers()->exists()) {
            return false;
        }

        return $user->can('districts.delete');
    }

    public function restore(User $user, District $district): bool
    {
        return $user->can('districts.restore');
    }

    public function forceDelete(User $user, District $district): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('districts.export');
    }

    public function import(User $user): bool
    {
        return $user->can('districts.import');
    }

    public function print(User $user): bool
    {
        return $user->can('districts.print');
    }
}
