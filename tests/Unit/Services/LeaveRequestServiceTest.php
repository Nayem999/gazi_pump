<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Leave from submission to decision, and what it does to attendance.
 *
 * The attendance rules are the ones worth guarding hardest: leave writing
 * over a day someone actually worked would destroy real check-in evidence,
 * and leave failing to clear an Absent row would leave people marked as
 * no-shows for time off their manager approved.
 */
class LeaveRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): LeaveRequestService
    {
        return app(LeaveRequestService::class);
    }

    /** A Monday, so a default range never drifts onto a weekend. */
    private function monday(): Carbon
    {
        return Carbon::parse('2026-09-14');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sfa.attendance.weekend_days', ['Friday', 'Saturday']);
    }

    public function test_it_counts_only_working_days(): void
    {
        // Mon 14 Sep to Sun 20 Sep: Friday and Saturday are the weekend
        // here, so five working days remain.
        $days = $this->service()->workingDaysBetween($this->monday(), $this->monday()->addDays(6));

        $this->assertSame(5.0, $days);
    }

    public function test_it_excludes_recorded_holidays(): void
    {
        Holiday::factory()->create(['date' => $this->monday()->toDateString(), 'status' => true]);

        $days = $this->service()->workingDaysBetween($this->monday(), $this->monday()->addDays(6));

        $this->assertSame(4.0, $days, 'the holiday must not be charged as leave');
    }

    public function test_a_half_day_counts_as_half(): void
    {
        $days = $this->service()->workingDaysBetween($this->monday(), $this->monday(), true);

        $this->assertSame(0.5, $days);
    }

    public function test_submitting_stores_the_working_day_count(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        $request = $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDays(6)->toDateString(),
            'reason' => 'Family wedding.',
        ]);

        $this->assertSame(LeaveStatus::Pending, $request->status);
        $this->assertSame('5.0', $request->days);
    }

    public function test_a_range_with_no_working_days_is_refused(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        // Friday and Saturday only - the whole range is weekend.
        $friday = Carbon::parse('2026-09-18');

        $this->expectException(ValidationException::class);

        $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $friday->toDateString(),
            'to_date' => $friday->addDay()->toDateString(),
            'reason' => 'Weekend only.',
        ]);
    }

    public function test_an_end_date_before_the_start_is_refused(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->subDay()->toDateString(),
            'reason' => 'Backwards.',
        ]);
    }

    public function test_a_half_day_spanning_two_dates_is_refused(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'is_half_day' => true,
            'reason' => 'Half of two days?',
        ]);
    }

    public function test_overlapping_a_pending_request_is_refused(): void
    {
        // Pending counts as occupying: without this, someone could stack
        // two requests for the same days and have both approved.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        LeaveRequest::factory()->for($user)->for($type)->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDays(2)->toDateString(),
        ]);

        $this->expectException(ValidationException::class);

        $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->addDay()->toDateString(),
            'to_date' => $this->monday()->addDays(3)->toDateString(),
            'reason' => 'Overlaps.',
        ]);
    }

    public function test_a_rejected_request_does_not_block_the_same_dates(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        LeaveRequest::factory()->for($user)->for($type)->rejected()->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
        ]);

        $request = $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'reason' => 'Asking again.',
        ]);

        $this->assertSame(LeaveStatus::Pending, $request->status);
    }

    public function test_another_users_leave_does_not_block_these_dates(): void
    {
        $type = LeaveType::factory()->create();
        LeaveRequest::factory()->for($type)->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
        ]);

        $request = $this->service()->submit(User::factory()->create(), [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'reason' => 'Different person.',
        ]);

        $this->assertSame(LeaveStatus::Pending, $request->status);
    }

    public function test_approving_marks_each_covered_day_as_leave_in_attendance(): void
    {
        $user = User::factory()->create();
        $manager = User::factory()->create();
        $type = LeaveType::factory()->create(['name' => 'Sick Leave']);

        $request = LeaveRequest::factory()->for($user)->for($type)->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'days' => 2,
        ]);

        $this->service()->approve($request, $manager);

        foreach ([0, 1] as $offset) {
            $this->assertDatabaseHas('attendances', [
                'user_id' => $user->id,
                'date' => $this->monday()->addDays($offset)->toDateString(),
                'status' => AttendanceStatus::Leave->value,
            ]);
        }
    }

    public function test_a_pending_request_touches_no_attendance(): void
    {
        // Otherwise a day could be made to vanish from attendance just by
        // asking for leave.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        $this->service()->submit($user, [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->toDateString(),
            'reason' => 'Pending only.',
        ]);

        $this->assertSame(0, Attendance::count());
    }

    public function test_approving_replaces_an_auto_marked_absent_row(): void
    {
        // The retroactive case: the nightly job marked them absent before
        // the leave was approved.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();
        $date = $this->monday();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'status' => AttendanceStatus::Absent->value,
        ]);

        $request = LeaveRequest::factory()->for($user)->for($type)->onDate($date)->create();

        $this->service()->approve($request, User::factory()->create());

        $this->assertSame(1, Attendance::where('user_id', $user->id)->count(), 'no duplicate row');
        $this->assertSame(
            AttendanceStatus::Leave,
            Attendance::where('user_id', $user->id)->first()->status,
        );
    }

    public function test_approving_never_overwrites_a_day_that_was_actually_worked(): void
    {
        // A real check-in is better evidence than the leave request;
        // erasing it would destroy the record of them being at work.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();
        $date = $this->monday();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'status' => AttendanceStatus::Present->value,
        ]);

        $request = LeaveRequest::factory()->for($user)->for($type)->onDate($date)->create();

        $this->service()->approve($request, User::factory()->create());

        $this->assertSame(
            AttendanceStatus::Present,
            Attendance::where('user_id', $user->id)->first()->status,
        );
    }

    public function test_approving_skips_weekends_and_holidays_in_attendance(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();
        $friday = Carbon::parse('2026-09-18');

        // Thursday to Sunday: Friday and Saturday are the weekend.
        $request = LeaveRequest::factory()->for($user)->for($type)->create([
            'from_date' => $friday->copy()->subDay()->toDateString(),
            'to_date' => $friday->copy()->addDays(2)->toDateString(),
            'days' => 2,
        ]);

        $this->service()->approve($request, User::factory()->create());

        $this->assertSame(2, Attendance::where('user_id', $user->id)->count());
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $user->id,
            'date' => $friday->toDateString(),
        ]);
    }

    public function test_a_decided_request_cannot_be_decided_again(): void
    {
        $request = LeaveRequest::factory()->approved()->create();

        $this->expectException(ValidationException::class);

        $this->service()->approve($request, User::factory()->create());
    }

    public function test_rejecting_records_the_decision_and_writes_no_attendance(): void
    {
        $request = LeaveRequest::factory()->create();
        $manager = User::factory()->create();

        $rejected = $this->service()->reject($request, $manager, 'No cover available.');

        $this->assertSame(LeaveStatus::Rejected, $rejected->status);
        $this->assertSame($manager->id, $rejected->approved_by);
        $this->assertSame('No cover available.', $rejected->decision_remarks);
        $this->assertSame(0, Attendance::count());
    }

    public function test_cancelling_approved_future_leave_removes_its_attendance_rows(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();

        $request = LeaveRequest::factory()->for($user)->for($type)
            ->onDate(Carbon::now()->addWeek()->next(Carbon::MONDAY))->create();

        $this->service()->approve($request, User::factory()->create());
        $this->assertSame(1, Attendance::count());

        $cancelled = $this->service()->cancel($request->fresh());

        $this->assertSame(LeaveStatus::Cancelled, $cancelled->status);
        $this->assertSame(0, Attendance::count(), 'the person is expected at work again');
    }

    public function test_cancelling_leaves_a_real_check_in_alone(): void
    {
        // They turned up after all. That row is theirs, not the leave
        // module's to delete.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create();
        $date = Carbon::now()->addWeek()->next(Carbon::MONDAY);

        $request = LeaveRequest::factory()->for($user)->for($type)->onDate($date)->create();
        $this->service()->approve($request, User::factory()->create());

        Attendance::where('user_id', $user->id)->update(['status' => AttendanceStatus::Present->value]);

        $this->service()->cancel($request->fresh());

        $this->assertSame(1, Attendance::where('user_id', $user->id)->count());
    }

    public function test_approved_leave_that_has_started_cannot_be_cancelled(): void
    {
        $request = LeaveRequest::factory()->approved()->create([
            'from_date' => Carbon::now()->subWeek()->toDateString(),
            'to_date' => Carbon::now()->subWeek()->addDay()->toDateString(),
        ]);

        $this->expectException(ValidationException::class);

        $this->service()->cancel($request);
    }

    public function test_a_rejected_request_cannot_be_cancelled(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->cancel(LeaveRequest::factory()->rejected()->create());
    }
}
