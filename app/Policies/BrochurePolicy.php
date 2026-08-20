<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brochure;
use App\Models\User;

class BrochurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('brochures.view');
    }

    public function view(User $user, Brochure $brochure): bool
    {
        return $user->can('brochures.view');
    }

    public function create(User $user): bool
    {
        return $user->can('brochures.add');
    }

    public function update(User $user, Brochure $brochure): bool
    {
        return $user->can('brochures.edit');
    }

    public function delete(User $user, Brochure $brochure): bool
    {
        return $user->can('brochures.delete');
    }

    public function restore(User $user, Brochure $brochure): bool
    {
        return $user->can('brochures.restore');
    }

    public function forceDelete(User $user, Brochure $brochure): bool
    {
        return $user->hasRole('Super Admin');
    }
}
