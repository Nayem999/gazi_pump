<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_but_not_delete_customers(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('customers.index'))->assertOk();

        $customer = Customer::factory()->create();
        $this->actingAs($executive)
            ->delete(route('customers.destroy', $customer))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_customer_with_gps(): void
    {
        $territory = Territory::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('customers.store'), [
            'customer_code' => 'CUST-TEST-01',
            'name' => 'Test Dealer',
            'type' => 'dealer',
            'phone' => '01712345678',
            'territory_id' => $territory->id,
            'gps_lat' => '23.8103000',
            'gps_lng' => '90.4125000',
            'status' => '1',
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::where('customer_code', 'CUST-TEST-01')->firstOrFail();
        $this->assertSame('dealer', $customer->type->value);
        $this->assertEquals(23.8103, (float) $customer->gps_lat);
    }

    public function test_invalid_gps_coordinates_are_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('customers.store'), [
            'customer_code' => 'CUST-TEST-02',
            'name' => 'Test Dealer',
            'type' => 'dealer',
            'phone' => '01712345678',
            'gps_lat' => '999',
            'gps_lng' => '90.4125000',
        ]);

        $response->assertSessionHasErrors('gps_lat');
    }

    public function test_customer_type_must_be_a_valid_enum_value(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('customers.store'), [
            'customer_code' => 'CUST-TEST-03',
            'name' => 'Test Dealer',
            'type' => 'wholesaler',
            'phone' => '01712345678',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_super_admin_can_view_edit_and_delete_a_customer(): void
    {
        $admin = $this->superAdmin();
        $customer = Customer::factory()->create();

        $this->actingAs($admin)->get(route('customers.show', $customer))->assertOk();

        $this->actingAs($admin)->put(route('customers.update', $customer), [
            'customer_code' => $customer->customer_code,
            'name' => 'Renamed Customer',
            'type' => $customer->type->value,
            'phone' => $customer->phone,
            'status' => '1',
        ])->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Renamed Customer']);

        $this->actingAs($admin)->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}
