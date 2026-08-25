<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, category_id?: string, sales_team_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator;
}
