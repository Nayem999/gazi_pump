<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisitRequest;

/**
 * Visit requests arrive from customer-portal submissions — admins only view
 * them and update their status, never create or delete them here.
 */
class VisitRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visit-requests.view');
    }

    public function view(User $user, VisitRequest $visitRequest): bool
    {
        return $user->can('visit-requests.view');
    }

    public function update(User $user, VisitRequest $visitRequest): bool
    {
        return $user->can('visit-requests.edit');
    }
}
