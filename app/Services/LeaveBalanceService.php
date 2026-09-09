<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\Contracts\LeaveBalanceRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\Contracts\LeaveTypeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Answers "how many days does this person have left".
 *
 * Entitlement is read from leave_balances; days used are summed from
 * approved requests every time. Nothing here maintains a running counter,
 * which is why the answer cannot drift away from the requests it claims to
 * summarise - see the leave_balances migration for the full reasoning.
 */
class LeaveBalanceService extends BaseCrudService
{
    public function __construct(
        private readonly LeaveBalanceRepositoryInterface $balances,
        private readonly LeaveRequestRepositoryInterface $requests,
        private readonly LeaveTypeRepositoryInterface $types,
    ) {
        parent::__construct($balances);
    }

    /**
     * @param  array{search?: string, year?: string, user_id?: string, leave_type_id?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->balances->paginateWithFilters($filters, $perPage);
    }

    /**
     * Creates or updates one entitlement, reviving a soft-deleted row for
     * the same person/type/year rather than inserting over it.
     *
     * The table is unique on (user_id, leave_type_id, year) and the model
     * soft-deletes, so a trashed row still holds the key while being
     * invisible to a default-scoped query - a plain create() there fails
     * on a duplicate-key error that reads as a bug rather than as "this
     * entitlement was deleted earlier".
     *
     * @param  array<string, mixed>  $data
     */
    public function saveEntitlement(array $data): LeaveBalance
    {
        $existing = $this->balances->findForUserTypeYear(
            (int) $data['user_id'],
            (int) $data['leave_type_id'],
            (int) $data['year'],
        );

        if ($existing === null) {
            return $this->create($data);
        }

        // forceFill so deleted_at can be cleared in the same write - an
        // entitlement being re-granted is an undelete, not a new record,
        // and keeping the id preserves its audit trail.
        $existing->forceFill($data + ['deleted_at' => null])->save();

        return $existing;
    }

    /**
     * Whether reviving a trashed row is what saveEntitlement() would do -
     * so the form can say so before it happens.
     */
    public function trashedEntitlementExists(int $userId, int $leaveTypeId, int $year): bool
    {
        return $this->balances->findForUserTypeYear($userId, $leaveTypeId, $year)?->trashed() ?? false;
    }

    /**
     * One person's balance for every active leave type in a year.
     *
     * @return Collection<int, object{leave_type: LeaveType, entitled: float, carried_forward: float, total: float, used: float, remaining: float}>
     */
    public function forUser(User $user, ?int $year = null): Collection
    {
        $year ??= (int) now()->format('Y');

        $balances = LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->get()
            ->keyBy('leave_type_id');

        return $this->types->activeTypes()->map(function (LeaveType $type) use ($balances, $user, $year) {
            $balance = $balances->get($type->id);

            // No entitlement row yet means nobody has set this person up
            // for this type: the type's own quota is the sensible default
            // to show, and it is what setUpFor() would write.
            $entitled = $balance ? (float) $balance->entitled_days : (float) $type->annual_quota;
            $carried = $balance ? (float) $balance->carried_forward_days : 0.0;
            $used = $this->usedDays($user, $type, $year);

            return (object) [
                'leave_type' => $type,
                'entitled' => $entitled,
                'carried_forward' => $carried,
                'total' => $entitled + $carried,
                'used' => $used,
                // Can legitimately go negative: a manager may approve
                // leave beyond entitlement, and hiding that behind a
                // max(0) would make the overdraft invisible.
                'remaining' => $entitled + $carried - $used,
            ];
        })->values();
    }

    /** Approved days of one type taken by one person in one year. */
    public function usedDays(User $user, LeaveType $type, ?int $year = null): float
    {
        return $this->requests->approvedDaysFor($user->id, $type->id, $year ?? (int) now()->format('Y'));
    }

    /** What is left of one type, after approved requests. */
    public function remainingDays(User $user, LeaveType $type, ?int $year = null): float
    {
        $year ??= (int) now()->format('Y');

        $balance = LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('leave_type_id', $type->id)
            ->where('year', $year)
            ->first();

        $total = $balance
            ? $balance->totalEntitlement()
            : (float) $type->annual_quota;

        return $total - $this->usedDays($user, $type, $year);
    }

    /**
     * Runs setUpFor() across several people at once - what the setup
     * screen's "Set up entitlements" action calls.
     *
     * @param  \Illuminate\Support\Collection<int, User>|array<int, User>  $users
     * @return array{users: int, created: int}
     */
    public function setUpForMany(iterable $users, ?int $year = null): array
    {
        $year ??= (int) now()->format('Y');
        $created = 0;
        $touched = 0;

        foreach ($users as $user) {
            $made = $this->setUpFor($user, $year);
            $created += $made;

            if ($made > 0) {
                $touched++;
            }
        }

        // `users` counts only those who actually gained a row, so re-running
        // reports 0 rather than implying everyone was changed again.
        return ['users' => $touched, 'created' => $created];
    }

    /**
     * Creates this year's entitlement rows for a user from each type's
     * default quota, leaving any row that already exists alone - an
     * individually adjusted entitlement must survive a re-run.
     *
     * @return int how many rows were created
     */
    public function setUpFor(User $user, ?int $year = null): int
    {
        $year ??= (int) now()->format('Y');
        $created = 0;

        foreach ($this->types->activeTypes() as $type) {
            // withTrashed via the repository: a deleted entitlement still
            // holds the unique key, so treating it as absent would fail on
            // the index. A deliberately deleted one is also not something
            // a bulk set-up should silently reinstate.
            if ($this->balances->findForUserTypeYear($user->id, $type->id, $year) !== null) {
                continue;
            }

            LeaveBalance::create([
                'user_id' => $user->id,
                'leave_type_id' => $type->id,
                'year' => $year,
                'entitled_days' => $type->annual_quota,
                'carried_forward_days' => 0,
            ]);

            $created++;
        }

        return $created;
    }
}
