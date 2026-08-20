<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;

/**
 * Inquiries arrive from the public portal (customers, guests, or the mobile
 * API) — admins only view them and update their status, never create or
 * delete them here.
 */
class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inquiries.view');
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $user->can('inquiries.view');
    }

    public function update(User $user, Inquiry $inquiry): bool
    {
        return $user->can('inquiries.edit');
    }
}
