<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface TerritoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, division_id?: string, district_id?: string, thana_id?: string, geo?: string, status?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
}
