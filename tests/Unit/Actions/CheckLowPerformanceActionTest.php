<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckLowPerformanceAction;
use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckLowPerformanceActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CheckLowPerformanceAction
    {
        return app(CheckLowPerformanceAction::class);
    }

    public function test_it_notifies_user_and_manager_for_low_grades(): void
    {
        $manager = User::factory()->create();
        $executive = User::factory()->create(['manager_id' => $manager->id]);
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'grade' => PerformanceGrade::F, 'overall_pct' => 20]);

        $count = $this->action()(8, 2026);

        $this->assertSame(1, $count);
        $this->assertCount(1, $executive->fresh()->notifications);
        $this->assertCount(1, $manager->fresh()->notifications);
    }

    public function test_it_ignores_high_grades(): void
    {
        $executive = User::factory()->create();
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'grade' => PerformanceGrade::A, 'overall_pct' => 95]);

        $count = $this->action()(8, 2026);

        $this->assertSame(0, $count);
    }

    public function test_it_ignores_a_different_period(): void
    {
        $executive = User::factory()->create();
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 7, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'grade' => PerformanceGrade::F, 'overall_pct' => 10]);

        $count = $this->action()(8, 2026);

        $this->assertSame(0, $count);
    }

    public function test_it_is_idempotent(): void
    {
        $executive = User::factory()->create();
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'grade' => PerformanceGrade::D, 'overall_pct' => 45]);

        $this->action()(8, 2026);
        $secondRunCount = $this->action()(8, 2026);

        $this->assertSame(0, $secondRunCount);
        $this->assertDatabaseCount('notifications', 1);
    }
}
