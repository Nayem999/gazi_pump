<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, product_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Sum of total_amount across every record matching the same filters as
     * paginateWithFilters(), unpaginated — used for the index page's total.
     *
     * @param  array{search?: string, user_id?: string, dealer_id?: string, product_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function sumWithFilters(array $filters): float;
}
