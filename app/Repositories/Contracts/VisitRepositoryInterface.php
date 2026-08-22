<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Visit;
use Illuminate\Pagination\LengthAwarePaginator;

interface VisitRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findOpenVisitForUser(int $userId): ?Visit;
}
