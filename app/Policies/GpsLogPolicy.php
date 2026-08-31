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
        return $user->can('gps-logs.view') && $this->isVisible($user, $gpsLog);
    }

    public function delete(User $user, GpsLog $gpsLog): bool
    {
        return $user->can('gps-logs.delete') && $this->isVisible($user, $gpsLog);
    }

    public function restore(User $user, GpsLog $gpsLog): bool
    {
        return $user->can('gps-logs.restore') && $this->isVisible($user, $gpsLog);
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

    /**
     * withTrashed() so restore/forceDelete (checked against an already
     * soft-deleted record) don't always fail.
     */
    private function isVisible(User $user, GpsLog $gpsLog): bool
    {
        return GpsLog::withTrashed()->visibleTo($user)->whereKey($gpsLog->id)->exists();
    }
}
