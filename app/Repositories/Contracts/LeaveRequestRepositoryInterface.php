<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeaveRequestRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, status?: string, leave_type_id?: string, user_id?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator;

    /**
     * Approved working days this user has taken of one type in one year.
     *
     * The source of truth for "days used" - see LeaveBalance for why this
     * is derived on read rather than kept in a column.
     */
    public function approvedDaysFor(int $userId, int $leaveTypeId, int $year): float;

    /**
     * Another request of this user's that already holds any of these
     * dates, ignoring $ignoreId so a request can be re-checked against
     * itself without colliding.
     */
    public function findOverlapping(int $userId, string $from, string $to, ?int $ignoreId = null): ?LeaveRequest;
}
