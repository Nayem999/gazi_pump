<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ProductCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, status?: string, parent_id?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
