<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\TerritoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TerritoryService extends BaseCrudService
{
    public function __construct(private readonly TerritoryRepositoryInterface $territories)
    {
        parent::__construct($territories);
    }

    /**
     * @param  array{search?: string, area_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->territories->paginateWithFilters($filters, $perPage);
    }
}
