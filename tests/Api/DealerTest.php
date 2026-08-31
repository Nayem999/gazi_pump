<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealerTest extends TestCase
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
    public function test_super_admin_can_list_dealers_via_api(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        Dealer::factory()->count(3)->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/dealers')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_dealer_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/dealers')->assertStatus(401);
        $this->postJson('/api/v1/dealers', [])->assertStatus(401);
    }

    public function test_index_defaults_to_the_authenticated_users_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        Dealer::factory()->count(2)->create(['territory_id' => $territoryA->id]);
        Dealer::factory()->create(['territory_id' => $territoryB->id]);

        $executive = User::factory()->inTerritory($territoryA)->create();
        $executive->assignRole('Sales Executive');

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/dealers')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_an_explicit_territory_id_cannot_widen_access_beyond_the_users_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        Dealer::factory()->count(2)->create(['territory_id' => $territoryA->id]);
        Dealer::factory()->create(['territory_id' => $territoryB->id]);

        $executive = User::factory()->inTerritory($territoryA)->create();
        $executive->assignRole('Sales Executive');

        // Requesting another territory's id no longer leaks its dealers —
        // the viewer's own territory is enforced unconditionally, so this
        // request just intersects with what's already visible (nothing).
        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/dealers?territory_id={$territoryB->id}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_sales_executive_can_register_a_new_dealer(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/dealers', [
                'dealer_code' => 'CUST-API-TEST',
                'name' => 'Field Registered Shop',
                'phone' => '01700000000',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.dealer_code', 'CUST-API-TEST')
            ->assertJsonPath('data.status', true);

        $this->assertDatabaseHas('dealers', ['dealer_code' => 'CUST-API-TEST']);
    }

    // Dealer outstanding-balance/ledger endpoints were retired along with
    // Order/Collection Entry (see the "version 1" Achievement pivot) — the
    // routes are removed from routes/api/v1.php, so their tests go with them.
}
