<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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

    public function test_super_admin_can_view_roles_list(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Super Admin')
            ->assertSee('Sales Executive');
    }

    public function test_super_admin_can_create_a_role_with_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('roles.store'), [
            'name' => 'Auditor',
            'permissions' => ['users.view', 'roles.view'],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'Auditor')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('roles.view'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));
    }

    public function test_protected_hierarchy_roles_cannot_be_deleted(): void
    {
        $role = Role::where('name', 'Sales Executive')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->delete(route('roles.destroy', $role))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_custom_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Temporary', 'guard_name' => 'web']);

        $this->actingAs($this->superAdmin())
            ->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_user_without_role_permission_is_forbidden(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)
            ->get(route('roles.index'))
            ->assertForbidden();
    }
}
