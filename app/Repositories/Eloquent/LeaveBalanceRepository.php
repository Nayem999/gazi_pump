<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\LeaveBalance;
use App\Repositories\Contracts\LeaveBalanceRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveBalanceRepository extends BaseRepository implements LeaveBalanceRepositoryInterface
{
    public function __construct(LeaveBalance $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'leaveType'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereHas(
                'user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%")
            ))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when($filters['leave_type_id'] ?? null, fn ($query, $id) => $query->where('leave_type_id', $id))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            // Grouped by person so one employee's types read together.
            ->orderByDesc('year')
            ->orderBy('user_id')
            ->orderBy('leave_type_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUserTypeYear(int $userId, int $leaveTypeId, int $year): ?LeaveBalance
    {
        return LeaveBalance::withTrashed()
            ->where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();
    }
}
