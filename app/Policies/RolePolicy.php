<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * The six hierarchy roles seeded in Module 1 are structural — they can
     * be edited (to adjust permissions) but never deleted.
     */
    private const PROTECTED_ROLES = [
        'Super Admin',
        'General Manager',
        'Sales Manager',
        'Area Manager',
        'Territory Manager',
        'Sales Executive',
    ];

    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.add');
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->name === 'Super Admin' && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $user->can('roles.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return false;
        }

        return $user->can('roles.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('roles.export');
    }

    public function print(User $user): bool
    {
        return $user->can('roles.print');
    }
}
