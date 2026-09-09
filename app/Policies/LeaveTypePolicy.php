<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leave-types.view');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave-types.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leave-types.add');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave-types.edit');
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave-types.delete');
    }

    public function restore(User $user, LeaveType $leaveType): bool
    {
        return $user->can('leave-types.restore');
    }

    public function forceDelete(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('leave-types.export');
    }

    public function import(User $user): bool
    {
        return $user->can('leave-types.import');
    }

    public function print(User $user): bool
    {
        return $user->can('leave-types.print');
    }
}
