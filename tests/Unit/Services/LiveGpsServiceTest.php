<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GpsLog;
use App\Models\Territory;
use App\Models\User;
use App\Services\LiveGpsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LiveGpsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // latestPositions() calls User::role('Sales Executive'), which
        // requires the role to exist.
        $this->seed(RolePermissionSeeder::class);
    }

    private function service(): LiveGpsService
    {
        return app(LiveGpsService::class);
    }

    private function executive(?int $territoryId = null): User
    {
        $user = $territoryId !== null
            ? User::factory()->inTerritory($territoryId)->create()
            : User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    /**
     * An unrestricted viewer (no territories, no sales team, not a plain
     * Sales Executive) — same visibility as General Manager/Super Admin,
     * so these tests keep exercising latestPositions()'s own filters
     * without the viewer-scoping tier also narrowing the result.
     */
    private function viewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    public function test_it_returns_each_executives_latest_position(): void
    {
        $executive = $this->executive();
        GpsLog::factory()->create(['user_id' => $executive->id, 'lat' => 23.70, 'lng' => 90.30, 'recorded_at' => Carbon::now()->subMinutes(10)]);
        GpsLog::factory()->create(['user_id' => $executive->id, 'lat' => 23.75, 'lng' => 90.35, 'recorded_at' => Carbon::now()->subMinutes(2)]);

        $row = $this->service()->latestPositions([], $this->viewer())->first();

        $this->assertSame(23.75, $row->lat);
        $this->assertSame(90.35, $row->lng);
    }

    public function test_a_recent_ping_is_not_stale(): void
    {
        $executive = $this->executive();
        GpsLog::factory()->create(['user_id' => $executive->id, 'recorded_at' => Carbon::now()->subMinutes(5)]);

        $row = $this->service()->latestPositions([], $this->viewer())->first();

        $this->assertFalse($row->is_stale);
    }

    public function test_a_ping_older_than_the_configured_window_is_stale(): void
    {
        config(['sfa.live_gps.stale_after_minutes' => 30]);
        $executive = $this->executive();
        GpsLog::factory()->create(['user_id' => $executive->id, 'recorded_at' => Carbon::now()->subMinutes(45)]);

        $row = $this->service()->latestPositions([], $this->viewer())->first();

        $this->assertTrue($row->is_stale);
    }

    public function test_executives_with_no_gps_logs_are_excluded(): void
    {
        $this->executive();

        $rows = $this->service()->latestPositions([], $this->viewer());

        $this->assertCount(0, $rows);
    }

    public function test_it_filters_by_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $inTerritory = $this->executive($territoryA->id);
        $outsideTerritory = $this->executive($territoryB->id);

        GpsLog::factory()->create(['user_id' => $inTerritory->id, 'recorded_at' => Carbon::now()]);
        GpsLog::factory()->create(['user_id' => $outsideTerritory->id, 'recorded_at' => Carbon::now()]);

        $rows = $this->service()->latestPositions(['territory_id' => (string) $territoryA->id], $this->viewer());

        $this->assertCount(1, $rows);
        $this->assertSame($inTerritory->id, $rows->first()->user->id);
    }

    public function test_it_filters_by_a_single_user(): void
    {
        $executiveA = $this->executive();
        $executiveB = $this->executive();
        GpsLog::factory()->create(['user_id' => $executiveA->id, 'recorded_at' => Carbon::now()]);
        GpsLog::factory()->create(['user_id' => $executiveB->id, 'recorded_at' => Carbon::now()]);

        $rows = $this->service()->latestPositions(['user_id' => (string) $executiveA->id], $this->viewer());

        $this->assertCount(1, $rows);
        $this->assertSame($executiveA->id, $rows->first()->user->id);
    }

    public function test_a_territory_scoped_viewer_only_sees_their_own_territorys_executives(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $inTerritory = $this->executive($territoryA->id);
        $outsideTerritory = $this->executive($territoryB->id);

        GpsLog::factory()->create(['user_id' => $inTerritory->id, 'recorded_at' => Carbon::now()]);
        GpsLog::factory()->create(['user_id' => $outsideTerritory->id, 'recorded_at' => Carbon::now()]);

        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territoryA);

        // No explicit territory_id filter — the viewer's own territory is
        // still enforced unconditionally.
        $rows = $this->service()->latestPositions([], $manager);

        $this->assertCount(1, $rows);
        $this->assertSame($inTerritory->id, $rows->first()->user->id);
    }
}
