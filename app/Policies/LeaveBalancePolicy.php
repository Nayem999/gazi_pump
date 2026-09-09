<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;

/**
 * Entitlements are HR policy, not field data: granting someone days is a
 * different act from approving a request against days they already have.
 * So this is a separate permission set from leave-requests.*, and a line
 * manager who can approve leave still cannot change how much leave people
 * are entitled to.
 */
class LeaveBalancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave-balances.view');
    }

    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('leave-balances.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leave-balances.add');
    }

    public function update(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('leave-balances.edit');
    }

    public function delete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('leave-balances.delete');
    }

    public function restore(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->can('leave-balances.restore');
    }

    public function forceDelete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('leave-balances.export');
    }

    public function import(User $user): bool
    {
        return $user->can('leave-balances.import');
    }

    public function print(User $user): bool
    {
        return $user->can('leave-balances.print');
    }
}
