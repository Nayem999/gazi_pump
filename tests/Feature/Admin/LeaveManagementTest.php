<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The admin side of Leave Management: who may see, file and decide leave,
 * and what a decision does to attendance.
 */
class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        config()->set('sfa.attendance.weekend_days', ['Friday', 'Saturday']);
    }

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function monday(): Carbon
    {
        return Carbon::parse('2026-09-14');
    }

    public function test_a_manager_can_see_the_leave_request_list(): void
    {
        LeaveRequest::factory()->create();

        $this->actingAs($this->generalManager())
            ->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSee('Leave Requests');
    }

    public function test_an_executive_submits_their_own_request(): void
    {
        $executive = $this->executive();
        $type = LeaveType::factory()->create();

        $this->actingAs($executive)->post(route('leave-requests.store'), [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'reason' => 'Family matter.',
        ])->assertRedirect(route('leave-requests.index'));

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $executive->id,
            'status' => LeaveStatus::Pending->value,
            'days' => '2.0',
        ]);
    }

    public function test_an_executive_cannot_file_leave_in_someone_elses_name(): void
    {
        // The tell-tale attack: a hidden user_id field pointed at a
        // colleague.
        $executive = $this->executive();
        $colleague = $this->executive();
        $type = LeaveType::factory()->create();

        $this->actingAs($executive)->post(route('leave-requests.store'), [
            'user_id' => $colleague->id,
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->toDateString(),
            'reason' => 'Not mine to file.',
        ])->assertSessionHasErrors('user_id');

        $this->assertSame(0, LeaveRequest::count());
    }

    public function test_a_manager_can_file_leave_for_someone_else(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $type = LeaveType::factory()->create();

        $this->actingAs($manager)->post(route('leave-requests.store'), [
            'user_id' => $executive->id,
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->toDateString(),
            'reason' => 'Called in sick by phone.',
        ])->assertRedirect();

        $this->assertDatabaseHas('leave_requests', ['user_id' => $executive->id]);
    }

    public function test_an_executive_only_sees_their_own_requests(): void
    {
        // Asserted on the employee name, which the list actually renders -
        // the reason is not a column.
        $executive = $this->executive();
        $executive->update(['name' => 'Own Request Owner']);

        $colleague = $this->executive();
        $colleague->update(['name' => 'Somebody Else Entirely']);

        LeaveRequest::factory()->for($executive)->create();
        LeaveRequest::factory()->for($colleague)->create();

        $this->actingAs($executive)->get(route('leave-requests.index'))
            ->assertOk()
            ->assertSee('Own Request Owner')
            ->assertDontSee('Somebody Else Entirely');
    }

    public function test_an_executive_cannot_open_a_colleagues_request(): void
    {
        $request = LeaveRequest::factory()->for($this->executive())->create();

        $this->actingAs($this->executive())
            ->get(route('leave-requests.show', $request))
            ->assertForbidden();
    }

    public function test_approving_marks_the_dates_as_leave_in_attendance(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $type = LeaveType::factory()->create();

        $request = LeaveRequest::factory()->for($executive)->for($type)->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->patch(route('leave-requests.approve', $request), ['decision_remarks' => 'Fine.'])
            ->assertRedirect();

        $this->assertSame(LeaveStatus::Approved, $request->fresh()->status);
        $this->assertSame(2, Attendance::where('status', AttendanceStatus::Leave)->count());
    }

    public function test_an_executive_cannot_approve_anything(): void
    {
        $request = LeaveRequest::factory()->create();

        $this->actingAs($this->executive())
            ->patch(route('leave-requests.approve', $request))
            ->assertForbidden();

        $this->assertSame(LeaveStatus::Pending, $request->fresh()->status);
    }

    public function test_a_manager_cannot_approve_their_own_leave(): void
    {
        // Holding the approve permission must not become a way to sign off
        // your own time off.
        $manager = $this->generalManager();
        $request = LeaveRequest::factory()->for($manager)->create();

        $this->actingAs($manager)
            ->patch(route('leave-requests.approve', $request))
            ->assertForbidden();

        $this->assertSame(LeaveStatus::Pending, $request->fresh()->status);
    }

    public function test_rejecting_requires_a_reason(): void
    {
        $request = LeaveRequest::factory()->create();

        $this->actingAs($this->generalManager())
            ->patch(route('leave-requests.reject', $request), [])
            ->assertSessionHasErrors('decision_remarks');

        $this->assertSame(LeaveStatus::Pending, $request->fresh()->status);
    }

    public function test_an_executive_can_withdraw_their_own_pending_request(): void
    {
        $executive = $this->executive();
        $request = LeaveRequest::factory()->for($executive)->create();

        $this->actingAs($executive)
            ->patch(route('leave-requests.cancel', $request))
            ->assertRedirect();

        $this->assertSame(LeaveStatus::Cancelled, $request->fresh()->status);
    }

    public function test_an_executive_cannot_withdraw_a_colleagues_request(): void
    {
        $request = LeaveRequest::factory()->for($this->executive())->create();

        $this->actingAs($this->executive())
            ->patch(route('leave-requests.cancel', $request))
            ->assertForbidden();
    }

    public function test_a_decided_request_can_no_longer_be_edited(): void
    {
        // Changing dates under a decision already made would make the
        // record misrepresent what was agreed.
        $request = LeaveRequest::factory()->approved()->create();

        $this->actingAs($this->generalManager())
            ->get(route('leave-requests.edit', $request))
            ->assertForbidden();
    }

    public function test_a_territory_manager_only_decides_their_own_territorys_leave(): void
    {
        $territory = Territory::factory()->create();
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territory);

        $outsider = $this->executive();
        $request = LeaveRequest::factory()->for($outsider)->create();

        $this->actingAs($manager)
            ->patch(route('leave-requests.approve', $request))
            ->assertForbidden();
    }

    public function test_the_balance_screen_shows_what_is_left(): void
    {
        $executive = $this->executive();
        $type = LeaveType::factory()->create(['name' => 'Casual Leave', 'annual_quota' => 10]);

        LeaveRequest::factory()->for($executive)->for($type)->approved()
            ->onDate($this->monday())->create();

        $this->actingAs($this->generalManager())
            ->get(route('leave-requests.balances', ['user_id' => $executive->id, 'year' => $this->monday()->year]))
            ->assertOk()
            ->assertSee('Casual Leave')
            // 10 entitled, 1 taken.
            ->assertSee('9');
    }

    public function test_an_executive_cannot_read_someone_elses_balance(): void
    {
        $executive = $this->executive();
        $colleague = $this->executive();

        $this->actingAs($executive)
            ->get(route('leave-requests.balances', ['user_id' => $colleague->id]))
            ->assertForbidden();
    }

    public function test_a_manager_can_manage_leave_types(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->post(route('leave-types.store'), [
            'name' => 'Casual Leave',
            'code' => 'CL',
            'annual_quota' => 10,
            'is_paid' => 1,
            'status' => 1,
        ])->assertRedirect(route('leave-types.index'));

        $this->assertDatabaseHas('leave_types', ['code' => 'CL', 'annual_quota' => 10]);
    }

    public function test_an_executive_cannot_reach_leave_types(): void
    {
        // Quotas are a policy decision, not a field one.
        $this->actingAs($this->executive())
            ->get(route('leave-types.index'))
            ->assertForbidden();
    }
}
