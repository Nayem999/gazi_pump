<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\SalesTeamRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesTeamService extends BaseCrudService
{
    public function __construct(private readonly SalesTeamRepositoryInterface $salesTeams)
    {
        parent::__construct($salesTeams);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->salesTeams->paginateWithFilters($filters, $perPage);
    }
}
