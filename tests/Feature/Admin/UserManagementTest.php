<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    private function salesExecutive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_gets_forbidden(): void
    {
        $this->actingAs($this->salesExecutive())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_user_list(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->superAdmin())
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('All Users');
    }

    public function test_super_admin_can_create_a_user(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('users.store'), [
            'employee_id' => 'EMP-55555',
            'name' => 'New Hire',
            'email' => 'new.hire@gazipump.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => '1',
            'roles' => ['Sales Executive'],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new.hire@gazipump.com', 'employee_id' => 'EMP-55555']);

        $created = User::where('email', 'new.hire@gazipump.com')->first();
        $this->assertTrue($created->hasRole('Sales Executive'));
    }

    public function test_super_admin_can_create_a_user_without_an_employee_id(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('users.store'), [
            'name' => 'No Employee Id',
            'email' => 'no.employee.id@gazipump.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'no.employee.id@gazipump.com', 'employee_id' => null]);
    }

    public function test_two_users_can_both_be_created_without_an_employee_id(): void
    {
        $this->actingAs($this->superAdmin())->post(route('users.store'), [
            'name' => 'First',
            'email' => 'first@gazipump.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $response = $this->actingAs($this->superAdmin())->post(route('users.store'), [
            'name' => 'Second',
            'email' => 'second@gazipump.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'))->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('users', ['email' => 'second@gazipump.com', 'employee_id' => null]);
    }

    public function test_creating_a_user_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@gazipump.com']);

        $response = $this->actingAs($this->superAdmin())->post(route('users.store'), [
            'employee_id' => 'EMP-66666',
            'name' => 'Duplicate',
            'email' => 'taken@gazipump.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_update_a_user(): void
    {
        $target = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->superAdmin())->put(route('users.update', $target), [
            'employee_id' => $target->employee_id,
            'name' => 'Updated Name',
            'email' => $target->email,
            'status' => '1',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated Name']);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_super_admin_can_soft_delete_and_restore_a_user(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $target))->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['id' => $target->id]);

        $this->actingAs($admin)->post(route('users.restore', $target->id))->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $target->id, 'deleted_at' => null]);
    }

    public function test_only_super_admin_can_permanently_delete_a_user(): void
    {
        $target = User::factory()->create();
        $target->delete();

        $generalManager = User::factory()->create();
        $generalManager->assignRole('General Manager');

        $this->actingAs($generalManager)
            ->delete(route('users.force-destroy', $target->id))
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->delete(route('users.force-destroy', $target->id))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
