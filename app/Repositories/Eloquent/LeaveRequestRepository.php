<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestRepository extends BaseRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(LeaveRequest $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'leaveType', 'approver'])
            // Applied in the repository, not the controller, so no caller
            // can forget it and show one executive another's leave.
            ->when($viewer, fn ($query, $user) => $query->visibleTo($user))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['leave_type_id'] ?? null, fn ($query, $id) => $query->where('leave_type_id', $id))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            // Overlap, not containment: a request running across the edge
            // of the filtered range is still leave taken within it.
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->where('to_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->where('from_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('from_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function approvedDaysFor(int $userId, int $leaveTypeId, int $year): float
    {
        return (float) $this->query()
            ->approved()
            ->where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            // Attributed by start date, matching LeaveRequest::year(): a
            // request spanning New Year draws entirely on the year it
            // began in, so its days can never be split across two
            // balances or counted twice.
            ->whereYear('from_date', $year)
            ->sum('days');
    }

    public function findOverlapping(int $userId, string $from, string $to, ?int $ignoreId = null): ?LeaveRequest
    {
        return $this->query()
            ->where('user_id', $userId)
            ->occupying()
            ->overlapping($from, $to)
            ->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))
            ->first();
    }
}
