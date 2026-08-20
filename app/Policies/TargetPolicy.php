<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Target;
use App\Models\User;

class TargetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('targets.view');
    }

    public function view(User $user, Target $target): bool
    {
        return $user->can('targets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('targets.add');
    }

    public function update(User $user, Target $target): bool
    {
        return $user->can('targets.edit');
    }

    public function delete(User $user, Target $target): bool
    {
        return $user->can('targets.delete');
    }

    public function restore(User $user, Target $target): bool
    {
        return $user->can('targets.restore');
    }

    public function forceDelete(User $user, Target $target): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('targets.export');
    }

    public function import(User $user): bool
    {
        return $user->can('targets.import');
    }

    public function print(User $user): bool
    {
        return $user->can('targets.print');
    }
}
