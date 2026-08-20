<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckBirthdayAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckBirthdayActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CheckBirthdayAction
    {
        return app(CheckBirthdayAction::class);
    }

    public function test_it_notifies_users_whose_birthday_is_today(): void
    {
        $manager = User::factory()->create();
        $celebrant = User::factory()->create([
            'manager_id' => $manager->id,
            'date_of_birth' => Carbon::now()->subYears(30)->format('Y-m-d'),
        ]);

        $count = $this->action()();

        $this->assertSame(1, $count);
        $this->assertCount(1, $celebrant->fresh()->notifications);
        $this->assertCount(1, $manager->fresh()->notifications);
    }

    public function test_it_ignores_users_whose_birthday_is_not_today(): void
    {
        User::factory()->create(['date_of_birth' => Carbon::now()->subYears(30)->subDay()->format('Y-m-d')]);

        $count = $this->action()();

        $this->assertSame(0, $count);
    }

    public function test_it_ignores_users_without_a_birth_date(): void
    {
        User::factory()->create(['date_of_birth' => null]);

        $count = $this->action()();

        $this->assertSame(0, $count);
    }

    public function test_it_is_idempotent_within_the_same_day(): void
    {
        User::factory()->create(['date_of_birth' => Carbon::now()->subYears(30)->format('Y-m-d')]);

        $this->action()();
        $secondRunCount = $this->action()();

        $this->assertSame(0, $secondRunCount);
        $this->assertDatabaseCount('notifications', 1);
    }
}
