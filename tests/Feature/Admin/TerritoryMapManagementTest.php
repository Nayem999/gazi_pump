<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoryMapManagementTest extends TestCase
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

    private function territoryManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Territory Manager');

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
        $this->get(route('territory-map.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_is_forbidden(): void
    {
        $this->actingAs($this->executive())->get(route('territory-map.index'))->assertForbidden();
    }

    public function test_general_manager_and_territory_manager_can_view_the_map(): void
    {
        Territory::factory()->create(['name' => 'North Dhaka Territory']);

        $this->actingAs($this->generalManager())->get(route('territory-map.index'))
            ->assertOk()
            ->assertSee('North Dhaka Territory');

        $this->actingAs($this->territoryManager())->get(route('territory-map.index'))->assertOk();
    }

    public function test_a_territory_manager_only_sees_their_own_territory_on_the_map(): void
    {
        $territoryA = Territory::factory()->create(['name' => 'Own Territory']);
        $territoryB = Territory::factory()->create(['name' => 'Other Territory']);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->get(route('territory-map.index'));

        $response->assertOk()->assertSee('Own Territory')->assertDontSee('Other Territory');
    }

    public function test_a_territory_manager_cannot_drill_into_a_territory_outside_their_own(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        $this->actingAs($manager)->getJson(route('territory-map.show', $territoryB))->assertForbidden();
        $this->actingAs($manager)->getJson(route('territory-map.show', $territoryA))->assertOk();
    }
}
