<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DriverService extends BaseCrudService
{
    public function __construct(private readonly DriverRepositoryInterface $drivers)
    {
        parent::__construct($drivers);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->drivers->paginateWithFilters($filters, $perPage);
    }
}
