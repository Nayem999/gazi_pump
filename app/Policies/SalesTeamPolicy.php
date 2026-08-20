<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SalesTeam;
use App\Models\User;

class SalesTeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sales-teams.view');
    }

    public function view(User $user, SalesTeam $salesTeam): bool
    {
        return $user->can('sales-teams.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sales-teams.add');
    }

    public function update(User $user, SalesTeam $salesTeam): bool
    {
        return $user->can('sales-teams.edit');
    }

    public function delete(User $user, SalesTeam $salesTeam): bool
    {
        return $user->can('sales-teams.delete');
    }

    public function restore(User $user, SalesTeam $salesTeam): bool
    {
        return $user->can('sales-teams.restore');
    }

    public function forceDelete(User $user, SalesTeam $salesTeam): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('sales-teams.export');
    }

    public function import(User $user): bool
    {
        return $user->can('sales-teams.import');
    }

    public function print(User $user): bool
    {
        return $user->can('sales-teams.print');
    }
}
