<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckNoCheckoutAction;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckNoCheckoutActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CheckNoCheckoutAction
    {
        return app(CheckNoCheckoutAction::class);
    }

    public function test_it_notifies_users_who_checked_in_but_never_checked_out_on_a_past_day(): void
    {
        $manager = User::factory()->create();
        $executive = User::factory()->create(['manager_id' => $manager->id]);

        Attendance::factory()->notCheckedOut()->create([
            'user_id' => $executive->id,
            'date' => Carbon::yesterday()->toDateString(),
            'check_in_at' => Carbon::yesterday()->setTime(9, 0),
        ]);

        $count = $this->action()();

        $this->assertSame(1, $count);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertCount(1, $manager->fresh()->notifications);
    }

    public function test_it_ignores_todays_incomplete_checkin(): void
    {
        $executive = User::factory()->create();

        Attendance::factory()->notCheckedOut()->create([
            'user_id' => $executive->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $count = $this->action()();

        $this->assertSame(0, $count);
    }

    public function test_it_ignores_completed_checkouts(): void
    {
        $executive = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $executive->id,
            'date' => Carbon::yesterday()->toDateString(),
        ]);

        $count = $this->action()();

        $this->assertSame(0, $count);
    }

    public function test_it_is_idempotent(): void
    {
        $executive = User::factory()->create();

        Attendance::factory()->notCheckedOut()->create([
            'user_id' => $executive->id,
            'date' => Carbon::yesterday()->toDateString(),
            'check_in_at' => Carbon::yesterday()->setTime(9, 0),
        ]);

        $this->action()();
        $secondRunCount = $this->action()();

        $this->assertSame(0, $secondRunCount);
        $this->assertDatabaseCount('notifications', 1);
    }
}
