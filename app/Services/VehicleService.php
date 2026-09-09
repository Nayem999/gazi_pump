<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleService extends BaseCrudService
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicles)
    {
        parent::__construct($vehicles);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->vehicles->paginateWithFilters($filters, $perPage);
    }
}
