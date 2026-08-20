<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\ServiceCenterRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceCenterService extends BaseCrudService
{
    public function __construct(private readonly ServiceCenterRepositoryInterface $serviceCenters)
    {
        parent::__construct($serviceCenters);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->serviceCenters->paginateWithFilters($filters, $perPage);
    }
}
