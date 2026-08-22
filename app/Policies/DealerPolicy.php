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
        return $user->can('dealers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dealers.add');
    }

    public function update(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.edit');
    }

    public function delete(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.delete');
    }

    public function restore(User $user, Dealer $dealer): bool
    {
        return $user->can('dealers.restore');
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
}
