<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\GpsLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GpsLogManagementTest extends TestCase
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

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('gps-logs.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('gps-logs.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_the_location_history(): void
    {
        $manager = $this->generalManager();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        GpsLog::factory()->count(3)->create(['user_id' => $executive->id, 'recorded_at' => now()]);

        $this->actingAs($manager)
            ->get(route('gps-logs.index', ['user_id' => $executive->id, 'date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('Travel Route');
    }

    public function test_general_manager_cannot_delete_a_gps_log(): void
    {
        $manager = $this->generalManager();
        $log = GpsLog::factory()->create();

        $this->actingAs($manager)->delete(route('gps-logs.destroy', $log))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_gps_log(): void
    {
        $admin = $this->superAdmin();
        $log = GpsLog::factory()->create();

        $this->actingAs($admin)->delete(route('gps-logs.destroy', $log))
            ->assertRedirect();
        $this->assertSoftDeleted('gps_logs', ['id' => $log->id]);

        $this->actingAs($admin)->post(route('gps-logs.restore', $log->id))
            ->assertRedirect();
        $this->assertDatabaseHas('gps_logs', ['id' => $log->id, 'deleted_at' => null]);
    }
}
