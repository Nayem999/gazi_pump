<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VisitRequestStatus;
use App\Models\VisitRequest;
use App\Repositories\Contracts\VisitRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VisitRequestService extends BaseCrudService
{
    public function __construct(private readonly VisitRequestRepositoryInterface $visitRequests)
    {
        parent::__construct($visitRequests);
    }

    /**
     * @param  array{search?: string, status?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visitRequests->paginateWithFilters($filters, $perPage);
    }

    public function updateStatus(VisitRequest $visitRequest, VisitRequestStatus $status): VisitRequest
    {
        /** @var VisitRequest */
        return $this->visitRequests->update($visitRequest, ['status' => $status]);
    }
}
