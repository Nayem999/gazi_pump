<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\DivisionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DivisionService extends BaseCrudService
{
    public function __construct(private readonly DivisionRepositoryInterface $divisions)
    {
        parent::__construct($divisions);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->divisions->paginateWithFilters($filters, $perPage);
    }
}
