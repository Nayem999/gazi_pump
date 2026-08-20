<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ServiceCenter;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCenterManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('service-centers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('service-centers.index'))->assertForbidden();
    }

    public function test_general_manager_can_create_a_service_center(): void
    {
        $response = $this->actingAs($this->generalManager())->post(route('service-centers.store'), [
            'name' => 'Dhaka Central Service Center',
            'address' => '123 Motijheel, Dhaka',
            'phone' => '01712345678',
            'lat' => '23.8103',
            'lng' => '90.4125',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('service-centers.index'));
        $this->assertDatabaseHas('service_centers', ['name' => 'Dhaka Central Service Center']);
    }

    public function test_lat_must_be_within_valid_range(): void
    {
        $this->actingAs($this->generalManager())->post(route('service-centers.store'), [
            'name' => 'Invalid Location',
            'lat' => '200',
        ])->assertSessionHasErrors('lat');
    }

    public function test_general_manager_can_update_delete_and_restore_a_service_center(): void
    {
        $manager = $this->generalManager();
        $serviceCenter = ServiceCenter::factory()->create();

        $this->actingAs($manager)->put(route('service-centers.update', $serviceCenter), [
            'name' => 'Renamed Service Center',
            'is_active' => '1',
        ])->assertRedirect(route('service-centers.index'));
        $this->assertDatabaseHas('service_centers', ['id' => $serviceCenter->id, 'name' => 'Renamed Service Center']);

        $this->actingAs($manager)->delete(route('service-centers.destroy', $serviceCenter))->assertRedirect(route('service-centers.index'));
        $this->assertSoftDeleted('service_centers', ['id' => $serviceCenter->id]);

        $this->actingAs($manager)->post(route('service-centers.restore', $serviceCenter->id))->assertRedirect(route('service-centers.index'));
        $this->assertDatabaseHas('service_centers', ['id' => $serviceCenter->id, 'deleted_at' => null]);
    }

    public function test_toggle_status_flips_is_active(): void
    {
        $manager = $this->generalManager();
        $serviceCenter = ServiceCenter::factory()->create(['is_active' => true]);

        $this->actingAs($manager)->patch(route('service-centers.toggle-status', $serviceCenter))->assertRedirect();

        $this->assertDatabaseHas('service_centers', ['id' => $serviceCenter->id, 'is_active' => false]);
    }
}
