<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckLateAttendanceAction;
use App\Models\Attendance;
use App\Models\User;
use App\Notifications\LateAttendanceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckLateAttendanceActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CheckLateAttendanceAction
    {
        return app(CheckLateAttendanceAction::class);
    }

    public function test_it_notifies_the_late_marked_user_and_their_manager(): void
    {
        $manager = User::factory()->create();
        $executive = User::factory()->create(['manager_id' => $manager->id]);
        $date = Carbon::yesterday();

        Attendance::factory()->late(30)->create([
            'user_id' => $executive->id,
            'date' => $date->toDateString(),
        ]);

        $count = $this->action()($date);

        $this->assertSame(1, $count);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertCount(1, $manager->fresh()->notifications);
    }

    public function test_it_ignores_present_and_absent_records(): void
    {
        $executive = User::factory()->create();
        $date = Carbon::yesterday();

        Attendance::factory()->create(['user_id' => $executive->id, 'date' => $date->toDateString()]);
        Attendance::factory()->absent()->create(['user_id' => $executive->id, 'date' => $date->copy()->subDay()->toDateString()]);

        $count = $this->action()($date);

        $this->assertSame(0, $count);
        $this->assertCount(0, $executive->fresh()->notifications);
    }

    public function test_it_is_idempotent_and_does_not_double_notify_on_rerun(): void
    {
        $executive = User::factory()->create();
        $date = Carbon::yesterday();

        Attendance::factory()->late(20)->create(['user_id' => $executive->id, 'date' => $date->toDateString()]);

        $this->action()($date);
        $secondRunCount = $this->action()($date);

        $this->assertSame(0, $secondRunCount);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_it_defaults_to_yesterday_when_no_date_given(): void
    {
        $executive = User::factory()->create();

        Attendance::factory()->late(15)->create(['user_id' => $executive->id, 'date' => Carbon::yesterday()->toDateString()]);

        $count = $this->action()();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('notifications', ['type' => LateAttendanceNotification::class]);
    }
}
