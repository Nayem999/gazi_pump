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
        return $user->can('orders.view') && $this->isVisible($user, $order);
    }

    public function create(User $user): bool
    {
        return $user->can('orders.add');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('orders.edit') && $this->isVisible($user, $order);
    }

    public function approve(User $user, Order $order): bool
    {
        return $user->can('orders.approve') && $this->isVisible($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.delete') && $this->isVisible($user, $order);
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->can('orders.restore') && $this->isVisible($user, $order);
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

    /**
     * Whether this order falls within the viewer's own visibility scope —
     * reuses Order::scopeVisibleTo() so list and single-record checks can
     * never drift out of sync with each other. withTrashed() so restore/
     * forceDelete (checked against an already soft-deleted record) don't
     * always fail.
     */
    private function isVisible(User $user, Order $order): bool
    {
        return Order::withTrashed()->visibleTo($user)->whereKey($order->id)->exists();
    }
}
