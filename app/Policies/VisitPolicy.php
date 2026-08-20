<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visits.view');
    }

    public function view(User $user, Visit $visit): bool
    {
        return $user->can('visits.view');
    }

    public function create(User $user): bool
    {
        return $user->can('visits.add');
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->can('visits.edit');
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->can('visits.delete');
    }

    public function restore(User $user, Visit $visit): bool
    {
        return $user->can('visits.restore');
    }

    public function forceDelete(User $user, Visit $visit): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('visits.export');
    }

    public function import(User $user): bool
    {
        return $user->can('visits.import');
    }

    public function print(User $user): bool
    {
        return $user->can('visits.print');
    }
}
