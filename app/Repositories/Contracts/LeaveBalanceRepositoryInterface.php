<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\LeaveBalance;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeaveBalanceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param  array{search?: string, year?: string, user_id?: string, leave_type_id?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * The entitlement row for one person, type and year - including a
     * soft-deleted one.
     *
     * withTrashed() deliberately: the table is unique on
     * (user_id, leave_type_id, year) and the model soft-deletes, so a
     * trashed row still occupies the key while being invisible to a
     * default-scoped query. Inserting over it fails on the index; callers
     * restore what this returns instead.
     */
    public function findForUserTypeYear(int $userId, int $leaveTypeId, int $year): ?LeaveBalance;
}
