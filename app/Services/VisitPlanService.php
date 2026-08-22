<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\VisitPlanRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    /**
     * Creates one Visit Plan per dealer id, all sharing the same executive/date/status/notes.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $dealerIds
     */
    public function createMany(array $data, array $dealerIds): int
    {
        return DB::transaction(function () use ($data, $dealerIds) {
            $count = 0;

            foreach ($dealerIds as $dealerId) {
                $this->visitPlans->create([...$data, 'dealer_id' => $dealerId]);
                $count++;
            }

            return $count;
        });
    }
}
