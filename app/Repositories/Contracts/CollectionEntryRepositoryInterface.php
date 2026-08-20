<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface CollectionEntryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, user_id?: string, customer_id?: string, payment_method?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
