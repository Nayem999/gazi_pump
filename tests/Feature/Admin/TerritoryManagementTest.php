<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoryManagementTest extends TestCase
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

    public function test_user_without_permission_gets_forbidden(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('territories.index'))->assertForbidden();
    }

    public function test_super_admin_can_create_a_territory_with_a_geojson_boundary(): void
    {
        $boundary = ['type' => 'Polygon', 'coordinates' => [[[90.4, 23.8], [90.42, 23.8], [90.42, 23.82], [90.4, 23.8]]]];

        $response = $this->actingAs($this->superAdmin())->post(route('territories.store'), [
            'name' => 'Test Territory',
            'code' => 'TER-TEST',
            'center_lat' => '23.8103',
            'center_lng' => '90.4125',
            'boundary' => json_encode($boundary),
            'status' => '1',
        ]);

        $response->assertRedirect(route('territories.index'));

        $territory = Territory::where('code', 'TER-TEST')->firstOrFail();
        $this->assertSame($boundary, $territory->boundary);
        $this->assertIsArray($territory->boundary);
    }

    public function test_territory_manager_must_be_a_valid_user(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('territories.store'), [
            'name' => 'Test Territory',
            'code' => 'TER-TEST',
            'manager_id' => 999999,
        ]);

        $response->assertSessionHasErrors('manager_id');
    }

    public function test_territory_with_assigned_users_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $territory = Territory::factory()->create();
        $executive = User::factory()->create(['territory_id' => $territory->id]);
        $executive->assignRole('Sales Executive');

        $this->actingAs($admin)
            ->delete(route('territories.destroy', $territory))
            ->assertForbidden();

        $this->assertDatabaseHas('territories', ['id' => $territory->id, 'deleted_at' => null]);
    }

    public function test_territory_without_assigned_users_can_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $territory = Territory::factory()->create();

        $this->actingAs($admin)
            ->delete(route('territories.destroy', $territory))
            ->assertRedirect(route('territories.index'));

        $this->assertSoftDeleted('territories', ['id' => $territory->id]);
    }
}
