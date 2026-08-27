<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CashHandover;
use App\Models\User;

class CashHandoverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cash-handovers.view');
    }

    public function view(User $user, CashHandover $cashHandover): bool
    {
        return $user->can('cash-handovers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cash-handovers.add');
    }

    public function confirm(User $user, CashHandover $cashHandover): bool
    {
        return $user->can('cash-handovers.approve');
    }

    public function delete(User $user, CashHandover $cashHandover): bool
    {
        return $user->can('cash-handovers.delete');
    }

    public function restore(User $user, CashHandover $cashHandover): bool
    {
        return $user->can('cash-handovers.restore');
    }

    public function forceDelete(User $user, CashHandover $cashHandover): bool
    {
        return $user->hasRole('Super Admin');
    }
}
