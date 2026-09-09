<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Driver;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverManagementTest extends TestCase
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

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function salesManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Manager');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('drivers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('drivers.index'))->assertForbidden();
    }

    public function test_a_manager_can_view_but_not_create_a_driver(): void
    {
        $manager = $this->salesManager();

        $this->actingAs($manager)->get(route('drivers.index'))->assertOk();

        $this->actingAs($manager)->post(route('drivers.store'), [
            'name' => 'Blocked Driver',
            'license_number' => 'LIC-BLOCK',
            'status' => '1',
        ])->assertForbidden();
    }

    public function test_general_manager_can_create_and_update_a_driver(): void
    {
        $manager = $this->generalManager();

        $response = $this->actingAs($manager)->post(route('drivers.store'), [
            'name' => 'John Doe',
            'license_number' => 'LIC-CENTRAL',
            'phone' => '01700000000',
            'status' => '1',
        ]);

        $response->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', ['license_number' => 'LIC-CENTRAL', 'name' => 'John Doe']);

        $driver = Driver::where('license_number', 'LIC-CENTRAL')->firstOrFail();

        $this->actingAs($manager)->put(route('drivers.update', $driver), [
            'name' => 'Jane Doe',
            'license_number' => $driver->license_number,
            'status' => '1',
        ])->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'name' => 'Jane Doe']);
    }

    public function test_super_admin_can_delete_and_restore_a_driver(): void
    {
        $admin = $this->superAdmin();
        $driver = Driver::factory()->create();

        $this->actingAs($admin)->delete(route('drivers.destroy', $driver))->assertRedirect(route('drivers.index'));
        $this->assertSoftDeleted('drivers', ['id' => $driver->id]);

        $this->actingAs($admin)->post(route('drivers.restore', $driver->id))->assertRedirect(route('drivers.index'));
        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'deleted_at' => null]);
    }

    public function test_license_number_must_be_unique(): void
    {
        Driver::factory()->create(['license_number' => 'LIC-1']);

        $this->actingAs($this->generalManager())->post(route('drivers.store'), [
            'name' => 'Duplicate',
            'license_number' => 'LIC-1',
        ])->assertSessionHasErrors('license_number');
    }
}
