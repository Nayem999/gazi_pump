<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('customers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('customers.add');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('customers.edit');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('customers.delete');
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->can('customers.restore');
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('customers.export');
    }

    public function import(User $user): bool
    {
        return $user->can('customers.import');
    }

    public function print(User $user): bool
    {
        return $user->can('customers.print');
    }
}
