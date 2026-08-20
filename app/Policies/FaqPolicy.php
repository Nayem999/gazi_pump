<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('faqs.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->can('faqs.view');
    }

    public function create(User $user): bool
    {
        return $user->can('faqs.add');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('faqs.edit');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('faqs.delete');
    }

    public function restore(User $user, Faq $faq): bool
    {
        return $user->can('faqs.restore');
    }

    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->hasRole('Super Admin');
    }
}
