<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\SalesTeam;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Order mobile self-service is retired for Sales Executive (see the
 * "version 1" Achievement pivot) — `api.orders.add`/`.view` are no longer
 * assigned to any role but Super Admin, so a plain Sales Executive now gets
 * 403 on every one of these endpoints. The underlying OrderService business
 * logic (price snapshotting, discount cap, territory/team validation) is
 * still real code, still worth covering — those cases now run as Super
 * Admin, the only role left with access, rather than being deleted.
 */
class OrderTest extends TestCase
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

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/orders', [])->assertStatus(401);
    }

    public function test_a_sales_executive_can_no_longer_record_a_mobile_order(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        // store() is gated by api.orders.add, which is now retired; index()
        // has no permission gate at all (pre-existing — it always scoped by
        // the caller's own user_id instead), so it stays reachable.
        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertForbidden();
    }

    public function test_an_authorized_user_can_record_an_order_with_multiple_products_at_current_prices(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        $productA = Product::factory()->create(['price' => 250]);
        $productB = Product::factory()->create(['price' => 40]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 4],
                    ['product_id' => $productB->id, 'quantity' => 2],
                ],
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data.items');

        $entry = Order::where('user_id', $admin->id)->firstOrFail();
        $this->assertCount(2, $entry->items);
        $this->assertSame('1080.00', (string) $entry->total_amount);
    }

    public function test_client_supplied_price_is_ignored_in_favor_of_the_products_current_price(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 250]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1], // not a validated field — must have no effect
                ],
            ])->assertStatus(201);

        $entry = Order::where('user_id', $admin->id)->firstOrFail();
        $this->assertSame('250.00', (string) $entry->items->first()->unit_price);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 10, 'discount_amount' => 500],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_at_least_one_item_is_required(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_index_only_returns_the_authenticated_users_own_orders(): void
    {
        $admin = $this->superAdmin();
        $otherAdmin = $this->superAdmin();

        Order::factory()->create(['user_id' => $admin->id, 'order_date' => Carbon::yesterday()->toDateString()]);
        Order::factory()->create(['user_id' => $otherAdmin->id, 'order_date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_a_recorded_order_reports_a_pending_status(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);

        $response->assertJsonPath('data.status', 'pending')->assertJsonPath('data.status_label', 'Pending');
    }

    public function test_an_order_can_optionally_be_placed_for_one_of_the_dealers_retailers(): void
    {
        $admin = $this->superAdmin();
        $dealer = Dealer::factory()->create();
        $retailer = Retailer::factory()->create(['dealer_id' => $dealer->id]);
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'retailer_id' => $retailer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['dealer_id' => $dealer->id, 'retailer_id' => $retailer->id]);
    }

    public function test_recording_an_order_for_a_dealer_outside_the_executives_territory_is_rejected(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $admin = User::factory()->inTerritory($territoryA)->create();
        $admin->assignRole('Super Admin');
        $dealer = Dealer::factory()->create(['territory_id' => $territoryB->id]);
        $product = Product::factory()->create(['price' => 100]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dealer_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_recording_an_order_with_a_product_outside_the_executives_sales_team_is_rejected(): void
    {
        $teamA = SalesTeam::factory()->create();
        $teamB = SalesTeam::factory()->create();
        $admin = User::factory()->create(['sales_team_id' => $teamA->id]);
        $admin->assignRole('Super Admin');
        $dealer = Dealer::factory()->create();
        $otherTeamProduct = Product::factory()->create(['sales_team_id' => $teamB->id, 'price' => 100]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->postJson('/api/v1/orders', [
                'dealer_id' => $dealer->id,
                'items' => [['product_id' => $otherTeamProduct->id, 'quantity' => 1]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.product_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_index_can_be_filtered_by_approval_status(): void
    {
        $admin = $this->superAdmin();
        Order::factory()->create(['user_id' => $admin->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $admin->id, 'status' => 'approved']);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($admin))
            ->getJson('/api/v1/orders?status=approved');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'approved');
    }
}
