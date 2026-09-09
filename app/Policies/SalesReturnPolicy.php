<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SalesReturn;
use App\Models\User;

class SalesReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales-returns.view');
    }

    public function view(User $user, SalesReturn $return): bool
    {
        return $user->can('sales-returns.view') && $this->isVisible($user, $return);
    }

    public function create(User $user): bool
    {
        return $user->can('sales-returns.add');
    }

    /**
     * Covers approve/reject/dispatch/receive — every status-transition
     * action on a return, gated by the same edit-level permission
     * (mirrors how Delivery's dispatch/deliver both use deliveries.add;
     * there's no separate "edit the request itself" form to distinguish
     * from these transitions).
     */
    public function update(User $user, SalesReturn $return): bool
    {
        return $user->can('sales-returns.edit') && $this->isVisible($user, $return);
    }

    public function approve(User $user, SalesReturn $return): bool
    {
        return $user->can('sales-returns.approve') && $this->isVisible($user, $return);
    }

    public function delete(User $user, SalesReturn $return): bool
    {
        return $user->can('sales-returns.delete') && $this->isVisible($user, $return);
    }

    public function restore(User $user, SalesReturn $return): bool
    {
        return $user->can('sales-returns.restore') && $this->isVisible($user, $return);
    }

    public function forceDelete(User $user, SalesReturn $return): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('sales-returns.export');
    }

    public function import(User $user): bool
    {
        return $user->can('sales-returns.import');
    }

    public function print(User $user): bool
    {
        return $user->can('sales-returns.print');
    }

    private function isVisible(User $user, SalesReturn $return): bool
    {
        return SalesReturn::withTrashed()->visibleTo($user)->whereKey($return->id)->exists();
    }
}
