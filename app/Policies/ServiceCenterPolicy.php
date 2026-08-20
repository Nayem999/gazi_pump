<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ServiceCenter;
use App\Models\User;

class ServiceCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('service-centers.view');
    }

    public function view(User $user, ServiceCenter $serviceCenter): bool
    {
        return $user->can('service-centers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('service-centers.add');
    }

    public function update(User $user, ServiceCenter $serviceCenter): bool
    {
        return $user->can('service-centers.edit');
    }

    public function delete(User $user, ServiceCenter $serviceCenter): bool
    {
        return $user->can('service-centers.delete');
    }

    public function restore(User $user, ServiceCenter $serviceCenter): bool
    {
        return $user->can('service-centers.restore');
    }

    public function forceDelete(User $user, ServiceCenter $serviceCenter): bool
    {
        return $user->hasRole('Super Admin');
    }
}
