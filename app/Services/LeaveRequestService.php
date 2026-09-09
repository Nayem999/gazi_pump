<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Leave from submission to decision, and the one place that writes leave
 * through to attendance.
 *
 * Two rules are worth stating up front because they shape everything else:
 *
 *  - **Only approved leave touches attendance.** A pending request changes
 *    no attendance record, so a person cannot make a day disappear from
 *    their attendance simply by asking.
 *  - **Approving never overwrites a day someone actually worked.** If a
 *    Present/Late/HalfDay row exists for a date, it stays; the check-in
 *    happened and erasing it would destroy the better evidence. Only an
 *    Absent row - or no row at all - becomes Leave.
 */
class LeaveRequestService extends BaseCrudService
{
    public function __construct(
        private readonly LeaveRequestRepositoryInterface $requests,
        private readonly AttendanceService $attendances,
        private readonly HolidayService $holidays,
        private readonly LeaveBalanceService $balances,
    ) {
        parent::__construct($requests);
    }

    public function paginate(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->requests->paginateWithFilters($filters, $perPage, $viewer);
    }

    /**
     * Working days between two dates, weekends and recorded holidays
     * removed. A half-day request is always a single date and counts 0.5.
     */
    public function workingDaysBetween(Carbon $from, Carbon $to, bool $isHalfDay = false): float
    {
        if ($isHalfDay) {
            return 0.5;
        }

        $days = 0.0;

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            if ($this->attendances->isWeekendDay($date) || $this->holidays->isHoliday($date)) {
                continue;
            }

            $days++;
        }

        return $days;
    }

    /**
     * Submits a request on $user's behalf.
     *
     * @param  array{leave_type_id: int|string, from_date: string, to_date: string, reason: string, is_half_day?: bool}  $data
     */
    public function submit(User $user, array $data): LeaveRequest
    {
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();
        $isHalfDay = (bool) ($data['is_half_day'] ?? false);

        if ($to->lt($from)) {
            throw ValidationException::withMessages([
                'to_date' => 'The end date cannot be before the start date.',
            ]);
        }

        if ($isHalfDay && ! $from->isSameDay($to)) {
            throw ValidationException::withMessages([
                'is_half_day' => 'A half day applies to a single date, so the start and end date must match.',
            ]);
        }

        $overlap = $this->requests->findOverlapping($user->id, $from->toDateString(), $to->toDateString());

        if ($overlap) {
            throw ValidationException::withMessages([
                'from_date' => sprintf(
                    'This overlaps a %s request from %s to %s.',
                    $overlap->status->label(),
                    $overlap->from_date->format('d M Y'),
                    $overlap->to_date->format('d M Y'),
                ),
            ]);
        }

        $days = $this->workingDaysBetween($from, $to, $isHalfDay);

        if ($days <= 0) {
            throw ValidationException::withMessages([
                'from_date' => 'That range contains no working days - it falls entirely on weekends or holidays.',
            ]);
        }

        return $this->create([
            'user_id' => $user->id,
            'leave_type_id' => $data['leave_type_id'],
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'days' => $days,
            'is_half_day' => $isHalfDay,
            'reason' => $data['reason'],
            'status' => LeaveStatus::Pending->value,
        ]);
    }

    /**
     * Approves a request and marks the covered days as leave in
     * attendance.
     *
     * Deliberately does NOT block on an insufficient balance. A manager
     * approving leave someone has not accrued is a real decision they are
     * entitled to make (unpaid leave, advance against next year); the
     * balance goes negative and stays visible, which is more useful than a
     * refusal the manager has to work around outside the system.
     */
    public function approve(LeaveRequest $leaveRequest, User $approver, ?string $remarks = null): LeaveRequest
    {
        $this->assertPending($leaveRequest);

        return DB::transaction(function () use ($leaveRequest, $approver, $remarks) {
            $this->update($leaveRequest, [
                'status' => LeaveStatus::Approved->value,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'decision_remarks' => $remarks,
            ]);

            $this->writeAttendance($leaveRequest->fresh());

            return $leaveRequest->fresh();
        });
    }

    public function reject(LeaveRequest $leaveRequest, User $approver, ?string $remarks = null): LeaveRequest
    {
        $this->assertPending($leaveRequest);

        $this->update($leaveRequest, [
            'status' => LeaveStatus::Rejected->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'decision_remarks' => $remarks,
        ]);

        return $leaveRequest->fresh();
    }

    /**
     * Withdraws a request.
     *
     * Allowed while pending, and also after approval for leave that has
     * not started yet - plans change, and the alternative is a manager
     * deleting the record, which loses the history. Once the leave has
     * begun it stays: attendance rows already exist for it and the days
     * were genuinely taken.
     */
    public function cancel(LeaveRequest $leaveRequest): LeaveRequest
    {
        if ($leaveRequest->status === LeaveStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => 'This request has already been cancelled.',
            ]);
        }

        if ($leaveRequest->status === LeaveStatus::Rejected) {
            throw ValidationException::withMessages([
                'status' => 'A rejected request cannot be cancelled.',
            ]);
        }

        if ($leaveRequest->isApproved() && $leaveRequest->from_date->isPast()) {
            throw ValidationException::withMessages([
                'status' => 'Approved leave that has already started cannot be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($leaveRequest) {
            // Approved leave wrote attendance rows; withdrawing it has to
            // take them back out, or the person stays "on leave" on a day
            // they are expected at work.
            if ($leaveRequest->isApproved()) {
                $this->clearAttendance($leaveRequest);
            }

            $this->update($leaveRequest, ['status' => LeaveStatus::Cancelled->value]);

            return $leaveRequest->fresh();
        });
    }

    /**
     * Marks every working day the request covers as Leave in attendance.
     *
     * An existing Present/Late/HalfDay row is left alone - see the class
     * note. An Absent row IS replaced, which is the retroactive case: the
     * nightly job marked someone absent before their leave was approved.
     */
    private function writeAttendance(LeaveRequest $leaveRequest): void
    {
        foreach ($this->coveredDates($leaveRequest) as $date) {
            $existing = Attendance::query()
                ->where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if ($existing && $existing->status->isWorked()) {
                continue;
            }

            // Phrased so it reads correctly whether or not the type name
            // already ends in "Leave" - "Casual Leave leave" otherwise.
            $remarks = sprintf(
                'On approved leave: %s (request #%d).',
                $leaveRequest->leaveType?->name ?? 'unspecified type',
                $leaveRequest->id,
            );

            if ($existing) {
                $existing->update(['status' => AttendanceStatus::Leave->value, 'remarks' => $remarks]);

                continue;
            }

            Attendance::create([
                'user_id' => $leaveRequest->user_id,
                'date' => $date->toDateString(),
                'status' => AttendanceStatus::Leave->value,
                'late_minutes' => 0,
                'remarks' => $remarks,
            ]);
        }
    }

    /**
     * Removes the Leave attendance rows this request created.
     *
     * Only rows still marked Leave are touched: if someone actually turned
     * up and checked in after all, that row is theirs and is left alone.
     */
    private function clearAttendance(LeaveRequest $leaveRequest): void
    {
        foreach ($this->coveredDates($leaveRequest) as $date) {
            Attendance::query()
                ->where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date->toDateString())
                ->where('status', AttendanceStatus::Leave)
                ->delete();
        }
    }

    /**
     * The working dates a request covers - the same weekend/holiday rules
     * the day count used, so attendance and `days` always agree.
     *
     * @return array<int, Carbon>
     */
    private function coveredDates(LeaveRequest $leaveRequest): array
    {
        $dates = [];

        for ($date = $leaveRequest->from_date->copy(); $date->lte($leaveRequest->to_date); $date->addDay()) {
            if ($this->attendances->isWeekendDay($date) || $this->holidays->isHoliday($date)) {
                continue;
            }

            $dates[] = $date->copy();
        }

        return $dates;
    }

    private function assertPending(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => "This request is already {$leaveRequest->status->label()} and cannot be decided again.",
            ]);
        }
    }

    /** The balance figures shown beside a request while deciding it. */
    public function balanceContextFor(LeaveRequest $leaveRequest): object
    {
        /** @var LeaveType $type */
        $type = $leaveRequest->leaveType;
        $user = $leaveRequest->user;
        $year = $leaveRequest->year();

        return (object) [
            'year' => $year,
            'used' => $this->balances->usedDays($user, $type, $year),
            'remaining' => $this->balances->remainingDays($user, $type, $year),
        ];
    }
}
