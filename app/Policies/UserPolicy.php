<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.add');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->can('users.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('users.restore');
    }

    /**
     * Permanent delete is Super Admin only, per the CRUD requirements spec.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->id !== $model->id && $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('users.export');
    }

    public function import(User $user): bool
    {
        return $user->can('users.import');
    }

    public function print(User $user): bool
    {
        return $user->can('users.print');
    }
}
