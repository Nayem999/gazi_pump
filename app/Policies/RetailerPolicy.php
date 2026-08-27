<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Retailer;
use App\Models\User;

class RetailerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('retailers.view');
    }

    public function view(User $user, Retailer $retailer): bool
    {
        return $user->can('retailers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('retailers.add');
    }

    public function update(User $user, Retailer $retailer): bool
    {
        return $user->can('retailers.edit');
    }

    public function delete(User $user, Retailer $retailer): bool
    {
        return $user->can('retailers.delete');
    }

    public function restore(User $user, Retailer $retailer): bool
    {
        return $user->can('retailers.restore');
    }

    public function forceDelete(User $user, Retailer $retailer): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('retailers.export');
    }

    public function import(User $user): bool
    {
        return $user->can('retailers.import');
    }

    public function print(User $user): bool
    {
        return $user->can('retailers.print');
    }
}
