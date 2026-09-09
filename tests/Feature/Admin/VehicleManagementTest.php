<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
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
        $this->get(route('vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('vehicles.index'))->assertForbidden();
    }

    public function test_a_manager_can_view_but_not_create_a_vehicle(): void
    {
        $manager = $this->salesManager();

        $this->actingAs($manager)->get(route('vehicles.index'))->assertOk();

        $this->actingAs($manager)->post(route('vehicles.store'), [
            'registration_number' => 'BLOCKED-1',
            'status' => '1',
        ])->assertForbidden();
    }

    public function test_general_manager_can_create_and_update_a_vehicle(): void
    {
        $manager = $this->generalManager();

        $response = $this->actingAs($manager)->post(route('vehicles.store'), [
            'registration_number' => 'DHA-1234',
            'type' => 'Truck',
            'capacity' => '2 Ton',
            'status' => '1',
        ]);

        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['registration_number' => 'DHA-1234', 'type' => 'Truck']);

        $vehicle = Vehicle::where('registration_number', 'DHA-1234')->firstOrFail();

        $this->actingAs($manager)->put(route('vehicles.update', $vehicle), [
            'registration_number' => $vehicle->registration_number,
            'type' => 'Van',
            'status' => '1',
        ])->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'type' => 'Van']);
    }

    public function test_super_admin_can_delete_and_restore_a_vehicle(): void
    {
        $admin = $this->superAdmin();
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($admin)->delete(route('vehicles.destroy', $vehicle))->assertRedirect(route('vehicles.index'));
        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);

        $this->actingAs($admin)->post(route('vehicles.restore', $vehicle->id))->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
    }

    public function test_registration_number_must_be_unique(): void
    {
        Vehicle::factory()->create(['registration_number' => 'DHA-1']);

        $this->actingAs($this->generalManager())->post(route('vehicles.store'), [
            'registration_number' => 'DHA-1',
        ])->assertSessionHasErrors('registration_number');
    }
}
