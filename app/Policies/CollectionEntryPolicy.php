<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CollectionEntry;
use App\Models\User;

class CollectionEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('collection-entries.view');
    }

    public function view(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.view') && $this->isVisible($user, $collectionEntry);
    }

    public function create(User $user): bool
    {
        return $user->can('collection-entries.add');
    }

    public function update(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.edit') && $this->isVisible($user, $collectionEntry);
    }

    public function approve(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.approve') && $this->isVisible($user, $collectionEntry);
    }

    public function delete(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.delete') && $this->isVisible($user, $collectionEntry);
    }

    public function restore(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.restore') && $this->isVisible($user, $collectionEntry);
    }

    public function forceDelete(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('collection-entries.export');
    }

    public function import(User $user): bool
    {
        return $user->can('collection-entries.import');
    }

    public function print(User $user): bool
    {
        return $user->can('collection-entries.print');
    }

    /**
     * Whether this collection falls within the viewer's own visibility
     * scope — reuses CollectionEntry::scopeVisibleTo() so list and
     * single-record checks can never drift out of sync with each other.
     * withTrashed() so restore/forceDelete (checked against an already
     * soft-deleted record) don't always fail.
     */
    private function isVisible(User $user, CollectionEntry $collectionEntry): bool
    {
        return CollectionEntry::withTrashed()->visibleTo($user)->whereKey($collectionEntry->id)->exists();
    }
}
