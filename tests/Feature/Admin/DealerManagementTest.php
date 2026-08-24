<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Dealer;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DealerManagementTest extends TestCase
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
        $this->get(route('dealers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_but_not_delete_dealers(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('dealers.index'))->assertOk();

        $dealer = Dealer::factory()->create();
        $this->actingAs($executive)
            ->delete(route('dealers.destroy', $dealer))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_dealer_with_gps(): void
    {
        $territory = Territory::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('dealers.store'), [
            'dealer_code' => 'CUST-TEST-01',
            'name' => 'Test Dealer',
            'type' => 'dealer',
            'phone' => '01712345678',
            'territory_id' => $territory->id,
            'gps_lat' => '23.8103000',
            'gps_lng' => '90.4125000',
            'status' => '1',
        ]);

        $response->assertRedirect(route('dealers.index'));

        $dealer = Dealer::where('dealer_code', 'CUST-TEST-01')->firstOrFail();
        $this->assertSame('dealer', $dealer->type->value);
        $this->assertEquals(23.8103, (float) $dealer->gps_lat);
    }

    public function test_super_admin_can_upload_a_dealer_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->superAdmin())->post(route('dealers.store'), [
            'dealer_code' => 'CUST-TEST-04',
            'name' => 'Photo Dealer',
            'type' => 'dealer',
            'phone' => '01712345678',
            'image' => UploadedFile::fake()->image('shop.jpg'),
        ]);

        $response->assertRedirect(route('dealers.index'));

        $dealer = Dealer::where('dealer_code', 'CUST-TEST-04')->firstOrFail();
        Storage::disk('public')->assertExists($dealer->image);
        $this->assertNotNull($dealer->imageUrl());
    }

    public function test_invalid_gps_coordinates_are_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('dealers.store'), [
            'dealer_code' => 'CUST-TEST-02',
            'name' => 'Test Dealer',
            'type' => 'dealer',
            'phone' => '01712345678',
            'gps_lat' => '999',
            'gps_lng' => '90.4125000',
        ]);

        $response->assertSessionHasErrors('gps_lat');
    }

    public function test_dealer_type_must_be_a_valid_enum_value(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('dealers.store'), [
            'dealer_code' => 'CUST-TEST-03',
            'name' => 'Test Dealer',
            'type' => 'wholesaler',
            'phone' => '01712345678',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_super_admin_can_view_edit_and_delete_a_dealer(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();

        $this->actingAs($admin)->get(route('dealers.show', $dealer))->assertOk();

        $this->actingAs($admin)->put(route('dealers.update', $dealer), [
            'dealer_code' => $dealer->dealer_code,
            'name' => 'Renamed Dealer',
            'type' => $dealer->type->value,
            'phone' => $dealer->phone,
            'status' => '1',
        ])->assertRedirect(route('dealers.index'));
        $this->assertDatabaseHas('dealers', ['id' => $dealer->id, 'name' => 'Renamed Dealer']);

        $this->actingAs($admin)->delete(route('dealers.destroy', $dealer))->assertRedirect(route('dealers.index'));
        $this->assertSoftDeleted('dealers', ['id' => $dealer->id]);
    }
}
