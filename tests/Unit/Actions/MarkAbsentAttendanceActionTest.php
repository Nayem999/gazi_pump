<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\MarkAbsentAttendanceAction;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MarkAbsentAttendanceActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function action(): MarkAbsentAttendanceAction
    {
        return app(MarkAbsentAttendanceAction::class);
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_it_marks_absent_every_executive_with_no_entry_on_a_working_day(): void
    {
        $executive = $this->executive();
        $date = Carbon::parse('2026-08-19'); // Wednesday

        $count = $this->action()($date);

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $executive->id,
            'date' => $date->toDateString(),
            'status' => AttendanceStatus::Absent->value,
        ]);
    }

    public function test_it_does_not_touch_a_day_that_already_has_an_entry(): void
    {
        $executive = $this->executive();
        $date = Carbon::parse('2026-08-19');

        Attendance::factory()->create(['user_id' => $executive->id, 'date' => $date->toDateString()]);

        $count = $this->action()($date);

        $this->assertSame(0, $count);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_it_skips_configured_weekend_days_entirely(): void
    {
        $this->executive();
        $date = Carbon::parse('2026-08-21'); // Friday — default weekend day

        $count = $this->action()($date);

        $this->assertSame(0, $count);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_it_ignores_inactive_users(): void
    {
        $executive = $this->executive();
        $executive->update(['status' => false]);
        $date = Carbon::parse('2026-08-19');

        $count = $this->action()($date);

        $this->assertSame(0, $count);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_it_defaults_to_yesterday_when_no_date_given(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-25 10:00:00')); // Tuesday, so yesterday is Monday

        $executive = $this->executive();

        $count = $this->action()();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $executive->id,
            'date' => '2026-08-24',
            'status' => AttendanceStatus::Absent->value,
        ]);
    }
}
