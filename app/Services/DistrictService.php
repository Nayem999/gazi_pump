<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\DistrictRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DistrictService extends BaseCrudService
{
    public function __construct(private readonly DistrictRepositoryInterface $districts)
    {
        parent::__construct($districts);
    }

    /**
     * @param  array{search?: string, division_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->districts->paginateWithFilters($filters, $perPage);
    }
}
