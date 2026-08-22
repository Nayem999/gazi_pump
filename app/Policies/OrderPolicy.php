<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.view');
    }

    public function create(User $user): bool
    {
        return $user->can('orders.add');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.edit');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.delete');
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->can('orders.restore');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('orders.export');
    }

    public function import(User $user): bool
    {
        return $user->can('orders.import');
    }

    public function print(User $user): bool
    {
        return $user->can('orders.print');
    }
}
