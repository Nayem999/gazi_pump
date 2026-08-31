<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Dealer;
use App\Models\User;

class DealerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dealers.view');
    }

    public function view(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.view') && $this->isVisible($user, $dealer);
    }

    public function create(User $user): bool
    {
        return $user->can('dealers.add');
    }

    public function update(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.edit') && $this->isVisible($user, $dealer);
    }

    public function delete(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.delete') && $this->isVisible($user, $dealer);
    }

    public function restore(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.restore') && $this->isVisible($user, $dealer);
    }

    public function forceDelete(User $user, Dealer $dealer): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('dealers.export');
    }

    public function import(User $user): bool
    {
        return $user->can('dealers.import');
    }

    public function print(User $user): bool
    {
        return $user->can('dealers.print');
    }

    /**
     * Whether this dealer falls within the viewer's own territories — reuses
     * Dealer::scopeVisibleTo() so list and single-record checks can never
     * drift out of sync with each other. withTrashed() so restore/forceDelete
     * (checked against an already soft-deleted record) don't always fail.
     */
    private function isVisible(User $user, Dealer $dealer): bool
    {
        return Dealer::withTrashed()->visibleTo($user)->whereKey($dealer->id)->exists();
    }
}
