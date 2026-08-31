<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface AchievementEntryRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator;
}
