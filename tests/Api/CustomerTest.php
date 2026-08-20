<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Customer;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('phpunit')->plainTextToken;
    }

    /**
     * Regression test: Spatie's guard resolution defaults to whichever guard
     * Laravel's Authenticate middleware last activated (Auth::shouldUse()),
     * which is 'sanctum' for API requests — but every permission in this app
     * is seeded under 'web'. Without User::$guard_name pinned to 'web', every
     * policy-gated API endpoint 403s for every role, including Super Admin.
     */
    public function test_super_admin_can_list_customers_via_api(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        Customer::factory()->count(3)->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_customer_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/customers')->assertStatus(401);
        $this->postJson('/api/v1/customers', [])->assertStatus(401);
    }

    public function test_index_defaults_to_the_authenticated_users_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        Customer::factory()->count(2)->create(['territory_id' => $territoryA->id]);
        Customer::factory()->create(['territory_id' => $territoryB->id]);

        $executive = User::factory()->create(['territory_id' => $territoryA->id]);
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_sales_executive_can_register_a_new_customer(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/customers', [
                'customer_code' => 'CUST-API-TEST',
                'name' => 'Field Registered Shop',
                'type' => 'retailer',
                'phone' => '01700000000',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer_code', 'CUST-API-TEST')
            ->assertJsonPath('data.status', true);

        $this->assertDatabaseHas('customers', ['customer_code' => 'CUST-API-TEST']);
    }

    public function test_registering_a_customer_requires_a_valid_type(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/customers', [
                'customer_code' => 'CUST-API-TEST-2',
                'name' => 'Field Registered Shop',
                'type' => 'wholesaler',
                'phone' => '01700000000',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
