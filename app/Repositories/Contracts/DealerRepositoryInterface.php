<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface DealerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, division_id?: string, district_id?: string, thana_id?: string, territory_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
