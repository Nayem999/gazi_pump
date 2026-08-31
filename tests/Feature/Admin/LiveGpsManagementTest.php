<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\GpsLog;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LiveGpsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('live-gps.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_is_forbidden_from_the_dashboard_and_the_json_endpoint(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('live-gps.index'))->assertForbidden();
        $this->actingAs($executive)->get(route('live-gps.positions'))->assertForbidden();
    }

    public function test_general_manager_can_view_the_dashboard(): void
    {
        $this->actingAs($this->generalManager())->get(route('live-gps.index'))
            ->assertOk()
            ->assertSee('Live GPS Dashboard');
    }

    public function test_positions_endpoint_returns_the_latest_ping_per_executive(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        GpsLog::factory()->create(['user_id' => $executive->id, 'lat' => 23.7, 'lng' => 90.3, 'recorded_at' => Carbon::now()->subMinutes(5)]);

        $response = $this->actingAs($manager)->getJson(route('live-gps.positions'));

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($executive->id, $response->json('data.0.userId'));
        $this->assertFalse($response->json('data.0.isStale'));
    }

    public function test_a_territory_manager_only_sees_positions_for_their_own_territorys_executives(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $inTerritory = User::factory()->inTerritory($territoryA)->create();
        $inTerritory->assignRole('Sales Executive');
        $outsideTerritory = User::factory()->inTerritory($territoryB)->create();
        $outsideTerritory->assignRole('Sales Executive');

        GpsLog::factory()->create(['user_id' => $inTerritory->id, 'recorded_at' => Carbon::now()]);
        GpsLog::factory()->create(['user_id' => $outsideTerritory->id, 'recorded_at' => Carbon::now()]);

        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->getJson(route('live-gps.positions'));

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($inTerritory->id, $response->json('data.0.userId'));
    }
}
