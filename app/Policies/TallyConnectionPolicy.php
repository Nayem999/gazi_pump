<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TallyConnection;
use App\Models\User;

class TallyConnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tally-integration.view');
    }

    public function view(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->can('tally-integration.view');
    }

    public function create(User $user): bool
    {
        return $user->can('tally-integration.configure');
    }

    public function update(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->can('tally-integration.configure');
    }

    public function delete(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->can('tally-integration.configure');
    }

    public function restore(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->can('tally-integration.configure');
    }

    public function forceDelete(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function testConnection(User $user, TallyConnection $tallyConnection): bool
    {
        return $user->can('tally-integration.sync');
    }
}
