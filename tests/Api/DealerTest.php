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

    /**
     * Phase 7: revived alongside the DealerController methods that already
     * existed (Order/Collection Entry's own revival in Phase 2 restored
     * these two controller actions but their routes were never re-added to
     * routes/api/v1.php — now fixed) — and improved to prefer the real
     * Tally-synced balance (Phase 5) over the SFA-only estimate.
     */
    public function test_outstanding_balance_falls_back_to_the_sfa_estimate_when_never_synced(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        \App\Models\Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        \App\Models\CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 300]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/dealers/{$dealer->id}/outstanding-balance");

        $response->assertOk()
            ->assertJsonPath('data.outstanding_balance', 700)
            ->assertJsonPath('data.source', 'estimate');
    }

    public function test_outstanding_balance_prefers_the_real_tally_ledger_once_synced(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        \App\Models\Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        \App\Models\LedgerEntry::factory()->create(['dealer_id' => $dealer->id, 'debit_amount' => 500, 'credit_amount' => 0]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/dealers/{$dealer->id}/outstanding-balance");

        $response->assertOk()
            ->assertJsonPath('data.outstanding_balance', 500)
            ->assertJsonPath('data.source', 'tally');
    }

    public function test_ledger_falls_back_to_the_order_collection_estimate_when_never_synced(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        \App\Models\Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => '2026-08-01', 'total_amount' => 1000]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/dealers/{$dealer->id}/ledger");

        $response->assertOk()->assertJsonCount(1, 'data.transactions');
    }

    public function test_ledger_prefers_real_tally_ledger_entries_once_any_exist(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();
        \App\Models\Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        \App\Models\LedgerEntry::factory()->create(['dealer_id' => $dealer->id, 'voucher_type' => 'Sales', 'debit_amount' => 750, 'credit_amount' => 0]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/dealers/{$dealer->id}/ledger");

        $response->assertOk()
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.balance', 750);
    }
}
