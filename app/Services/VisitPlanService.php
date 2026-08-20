<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\VisitPlanRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VisitPlanService extends BaseCrudService
{
    public function __construct(private readonly VisitPlanRepositoryInterface $visitPlans)
    {
        parent::__construct($visitPlans);
    }

    /**
     * @param  array{search?: string, user_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visitPlans->paginateWithFilters($filters, $perPage);
    }
}
