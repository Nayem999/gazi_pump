<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisitPlan;

class VisitPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visit-plans.view');
    }

    public function view(User $user, VisitPlan $visitPlan): bool
    {
        return $user->can('visit-plans.view');
    }

    public function create(User $user): bool
    {
        return $user->can('visit-plans.add');
    }

    public function update(User $user, VisitPlan $visitPlan): bool
    {
        return $user->can('visit-plans.edit');
    }

    public function delete(User $user, VisitPlan $visitPlan): bool
    {
        return $user->can('visit-plans.delete');
    }

    public function restore(User $user, VisitPlan $visitPlan): bool
    {
        return $user->can('visit-plans.restore');
    }

    public function forceDelete(User $user, VisitPlan $visitPlan): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('visit-plans.export');
    }

    public function import(User $user): bool
    {
        return $user->can('visit-plans.import');
    }

    public function print(User $user): bool
    {
        return $user->can('visit-plans.print');
    }
}
