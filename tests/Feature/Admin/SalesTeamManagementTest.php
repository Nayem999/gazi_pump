<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\SalesTeam;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('sales-teams.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_gets_forbidden(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('sales-teams.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_and_create_a_sales_team(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('sales-teams.index'))->assertOk();

        $response = $this->actingAs($admin)->post(route('sales-teams.store'), [
            'name' => 'Team 4',
            'code' => 'TEAM-4',
            'description' => 'A new team',
            'status' => '1',
        ]);

        $response->assertRedirect(route('sales-teams.index'));
        $this->assertDatabaseHas('sales_teams', ['code' => 'TEAM-4', 'name' => 'Team 4']);
    }

    public function test_code_must_be_unique(): void
    {
        SalesTeam::factory()->create(['code' => 'TEAM-1']);

        $response = $this->actingAs($this->superAdmin())->post(route('sales-teams.store'), [
            'name' => 'Duplicate',
            'code' => 'TEAM-1',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_super_admin_can_update_delete_and_restore_a_sales_team(): void
    {
        $admin = $this->superAdmin();
        $team = SalesTeam::factory()->create();

        $this->actingAs($admin)->put(route('sales-teams.update', $team), [
            'name' => 'Renamed Team',
            'code' => $team->code,
            'status' => '1',
        ])->assertRedirect(route('sales-teams.index'));
        $this->assertDatabaseHas('sales_teams', ['id' => $team->id, 'name' => 'Renamed Team']);

        $this->actingAs($admin)->delete(route('sales-teams.destroy', $team))->assertRedirect(route('sales-teams.index'));
        $this->assertSoftDeleted('sales_teams', ['id' => $team->id]);

        $this->actingAs($admin)->post(route('sales-teams.restore', $team->id))->assertRedirect(route('sales-teams.index'));
        $this->assertDatabaseHas('sales_teams', ['id' => $team->id, 'deleted_at' => null]);
    }
}
