<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Thana;
use App\Models\User;

class ThanaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('thanas.view');
    }

    public function view(User $user, Thana $thana): bool
    {
        return $user->can('thanas.view');
    }

    public function create(User $user): bool
    {
        return $user->can('thanas.add');
    }

    public function update(User $user, Thana $thana): bool
    {
        return $user->can('thanas.edit');
    }

    public function delete(User $user, Thana $thana): bool
    {
        if ($thana->territories()->exists() || $thana->dealers()->exists()) {
            return false;
        }

        return $user->can('thanas.delete');
    }

    public function restore(User $user, Thana $thana): bool
    {
        return $user->can('thanas.restore');
    }

    public function forceDelete(User $user, Thana $thana): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('thanas.export');
    }

    public function import(User $user): bool
    {
        return $user->can('thanas.import');
    }

    public function print(User $user): bool
    {
        return $user->can('thanas.print');
    }
}
