<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckTargetReminderAction;
use App\Models\Achievement;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckTargetReminderActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CheckTargetReminderAction
    {
        return app(CheckTargetReminderAction::class);
    }

    public function test_it_does_nothing_outside_the_reminder_window(): void
    {
        $today = Carbon::create(2026, 8, 1);
        $executive = User::factory()->create();
        Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $count = $this->action()($today);

        $this->assertSame(0, $count);
    }

    public function test_it_notifies_when_behind_pace_within_the_window(): void
    {
        $manager = User::factory()->create();
        $executive = User::factory()->create(['manager_id' => $manager->id]);
        $today = Carbon::create(2026, 8, 29); // 2 days left in August
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'overall_pct' => 30]);

        $count = $this->action()($today);

        $this->assertSame(1, $count);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertCount(1, $manager->fresh()->notifications);
    }

    public function test_it_does_not_notify_when_on_pace(): void
    {
        $executive = User::factory()->create();
        $today = Carbon::create(2026, 8, 29);
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'overall_pct' => 85]);

        $count = $this->action()($today);

        $this->assertSame(0, $count);
    }

    public function test_it_notifies_a_target_with_no_achievement_yet(): void
    {
        $executive = User::factory()->create();
        $today = Carbon::create(2026, 8, 29);
        Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $count = $this->action()($today);

        $this->assertSame(1, $count);
    }

    public function test_it_is_idempotent_within_the_same_month(): void
    {
        $executive = User::factory()->create();
        $today = Carbon::create(2026, 8, 29);
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'overall_pct' => 20]);

        $this->action()($today);
        $secondRunCount = $this->action()($today);

        $this->assertSame(0, $secondRunCount);
        $this->assertDatabaseCount('notifications', 1);
    }
}
