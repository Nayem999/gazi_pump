<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GpsLog;
use App\Models\User;

class GpsLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gps-logs.view');
    }

    public function view(User $user, GpsLog $gpsLog): bool
    {
        return $user->can('gps-logs.view');
    }

    public function delete(User $user, GpsLog $gpsLog): bool
    {
        return $user->can('gps-logs.delete');
    }

    public function restore(User $user, GpsLog $gpsLog): bool
    {
        return $user->can('gps-logs.restore');
    }

    public function forceDelete(User $user, GpsLog $gpsLog): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('gps-logs.export');
    }

    public function print(User $user): bool
    {
        return $user->can('gps-logs.print');
    }
}
