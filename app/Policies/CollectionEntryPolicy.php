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
        return $user->can('collection-entries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('collection-entries.add');
    }

    public function update(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.edit');
    }

    public function approve(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.approve');
    }

    public function delete(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.delete');
    }

    public function restore(User $user, CollectionEntry $collectionEntry): bool
    {
        return $user->can('collection-entries.restore');
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
}
