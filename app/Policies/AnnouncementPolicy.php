<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

/**
 * Announcements are one-way broadcasts — created and (soft) deleted, never
 * edited or exported/printed, so this policy is intentionally smaller than
 * the full CRUD set other modules expose.
 */
class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('announcements.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('announcements.view');
    }

    public function create(User $user): bool
    {
        return $user->can('announcements.add');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('announcements.delete');
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->can('announcements.restore');
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->hasRole('Super Admin');
    }
}
