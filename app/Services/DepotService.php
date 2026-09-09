<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\DepotRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DepotService extends BaseCrudService
{
    public function __construct(private readonly DepotRepositoryInterface $depots)
    {
        parent::__construct($depots);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->depots->paginateWithFilters($filters, $perPage);
    }
}
