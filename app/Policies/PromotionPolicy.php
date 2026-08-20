<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Promotion;
use App\Models\User;

class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('promotions.view');
    }

    public function view(User $user, Promotion $promotion): bool
    {
        return $user->can('promotions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('promotions.add');
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $user->can('promotions.edit');
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        return $user->can('promotions.delete');
    }

    public function restore(User $user, Promotion $promotion): bool
    {
        return $user->can('promotions.restore');
    }

    public function forceDelete(User $user, Promotion $promotion): bool
    {
        return $user->hasRole('Super Admin');
    }
}
