<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface RetailerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, dealer_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
