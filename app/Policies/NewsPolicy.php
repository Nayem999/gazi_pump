<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('news.view');
    }

    public function view(User $user, News $news): bool
    {
        return $user->can('news.view');
    }

    public function create(User $user): bool
    {
        return $user->can('news.add');
    }

    public function update(User $user, News $news): bool
    {
        return $user->can('news.edit');
    }

    public function delete(User $user, News $news): bool
    {
        return $user->can('news.delete');
    }

    public function restore(User $user, News $news): bool
    {
        return $user->can('news.restore');
    }

    public function forceDelete(User $user, News $news): bool
    {
        return $user->hasRole('Super Admin');
    }
}
