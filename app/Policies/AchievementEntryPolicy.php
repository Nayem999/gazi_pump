<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AchievementEntry;
use App\Models\User;

class AchievementEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('achievements.view');
    }

    public function view(User $user, AchievementEntry $entry): bool
    {
        return $user->can('achievements.view') && $this->isVisible($user, $entry);
    }

    public function create(User $user): bool
    {
        return $user->can('achievements.add');
    }

    public function update(User $user, AchievementEntry $entry): bool
    {
        return $user->can('achievements.edit') && $this->isVisible($user, $entry);
    }

    public function approve(User $user, AchievementEntry $entry): bool
    {
        return $user->can('achievements.approve') && $this->isVisible($user, $entry);
    }

    public function delete(User $user, AchievementEntry $entry): bool
    {
        return $user->can('achievements.delete') && $this->isVisible($user, $entry);
    }

    public function restore(User $user, AchievementEntry $entry): bool
    {
        return $user->can('achievements.restore') && $this->isVisible($user, $entry);
    }

    public function forceDelete(User $user, AchievementEntry $entry): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('achievements.export');
    }

    public function import(User $user): bool
    {
        return $user->can('achievements.import');
    }

    public function print(User $user): bool
    {
        return $user->can('achievements.print');
    }

    /**
     * Whether this entry falls within the viewer's own visibility scope —
     * reuses AchievementEntry::scopeVisibleTo() so list and single-record
     * checks can never drift out of sync with each other. withTrashed() so
     * restore/forceDelete (checked against an already soft-deleted record)
     * don't always fail.
     */
    private function isVisible(User $user, AchievementEntry $entry): bool
    {
        return AchievementEntry::withTrashed()->visibleTo($user)->whereKey($entry->id)->exists();
    }
}
