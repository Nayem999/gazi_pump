<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('attendance.add');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.edit');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.delete');
    }

    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.restore');
    }

    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('attendance.export');
    }

    public function import(User $user): bool
    {
        return $user->can('attendance.import');
    }

    public function print(User $user): bool
    {
        return $user->can('attendance.print');
    }
}
