<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Target;
use App\Models\User;

class TargetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('targets.view');
    }

    public function view(User $user, Target $target): bool
    {
        return $user->can('targets.view') && $this->isVisible($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->can('targets.add');
    }

    public function update(User $user, Target $target): bool
    {
        return $user->can('targets.edit') && $this->isVisible($user, $target);
    }

    public function delete(User $user, Target $target): bool
    {
        return $user->can('targets.delete') && $this->isVisible($user, $target);
    }

    public function restore(User $user, Target $target): bool
    {
        return $user->can('targets.restore') && $this->isVisible($user, $target);
    }

    public function forceDelete(User $user, Target $target): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('targets.export');
    }

    public function import(User $user): bool
    {
        return $user->can('targets.import');
    }

    public function print(User $user): bool
    {
        return $user->can('targets.print');
    }

    /**
     * Whether this target falls within the viewer's own visibility scope —
     * reuses Target::scopeVisibleTo() so list and single-record checks can
     * never drift out of sync with each other. withTrashed() so restore/
     * forceDelete (checked against an already soft-deleted record) don't
     * always fail.
     */
    private function isVisible(User $user, Target $target): bool
    {
        return Target::withTrashed()->visibleTo($user)->whereKey($target->id)->exists();
    }
}
