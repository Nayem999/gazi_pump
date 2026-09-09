<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\LeaveTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveTypeService extends BaseCrudService
{
    public function __construct(private readonly LeaveTypeRepositoryInterface $types)
    {
        parent::__construct($types);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->types->paginateWithFilters($filters, $perPage);
    }

    /**
     * @return Collection<int, \App\Models\LeaveType>
     */
    public function activeTypes(): Collection
    {
        return $this->types->activeTypes();
    }
}
