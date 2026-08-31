<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

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
 * Orders are retired for every role but Super Admin (see the "version 1"
 * Achievement pivot) — `orders.*`/`menu.orders` are no longer assigned to
 * General Manager, Sales/Area/Territory Manager, or Sales Executive. The
 * underlying OrderService/Policy business logic (territory/team scoping,
 * discount cap, approve/reject forward-only) is still real code, still
 * worth covering — those cases now run as Super Admin, the only role left
 * with access, rather than being deleted.
 */
class OrderManagementTest extends TestCase
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

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function orderWithItem(Product $product, int $quantity = 5, float $unitPrice = 100): Order
    {
        $entry = Order::factory()->create(['total_amount' => $quantity * $unitPrice]);
        $entry->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => 0,
            'total_amount' => $quantity * $unitPrice,
        ]);

        return $entry;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_no_longer_has_any_web_access(): void
    {
        $executive = $this->executive();
        $order = Order::factory()->create(['user_id' => $executive->id]);

        $this->actingAs($executive)->get(route('orders.index'))->assertForbidden();
        $this->actingAs($executive)->get(route('orders.create'))->assertForbidden();
        $this->actingAs($executive)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_general_manager_and_territory_manager_no_longer_have_any_web_access(): void
    {
        foreach (['General Manager', 'Territory Manager', 'Sales Manager', 'Area Manager'] as $role) {
            $manager = User::factory()->create();
            $manager->assignRole($role);

            $this->actingAs($manager)->get(route('orders.index'))->assertForbidden();
            $this->actingAs($manager)->get(route('orders.create'))->assertForbidden();
        }
    }

    public function test_a_territory_scoped_viewer_can_only_pick_a_dealer_in_their_own_territory_on_the_order_form(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        // orders.add is now Super Admin only — territories can still be
        // assigned to any role via the Users form, so this exercises a
        // regionally-scoped Super Admin.
        $admin = $this->superAdmin();
        $admin->territories()->attach($territoryA);

        $response = $this->actingAs($admin)->get(route('orders.create'));

        $response->assertOk()->assertSee($dealerA->name)->assertDontSee($dealerB->name);
    }

    public function test_recording_an_order_for_a_dealer_outside_the_viewers_territory_is_rejected(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);
        $product = Product::factory()->create(['price' => 100]);

        $admin = $this->superAdmin();
        $admin->territories()->attach($territoryA);
        $executive = $this->executive();

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealerB->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('dealer_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_super_admin_can_view_and_record_an_order_with_multiple_products(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $productA = Product::factory()->create(['price' => 100]);
        $productB = Product::factory()->create(['price' => 50]);

        $this->actingAs($admin)->get(route('orders.index'))->assertOk();

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 50],
                ['product_id' => $productB->id, 'quantity' => 2, 'unit_price' => 50, 'discount_amount' => 0],
            ],
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'total_amount' => 550,
        ]);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_an_order_can_optionally_be_placed_for_a_specific_retailer(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $retailer = Retailer::factory()->create(['dealer_id' => $dealer->id]);
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'retailer_id' => $retailer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', ['dealer_id' => $dealer->id, 'retailer_id' => $retailer->id]);
    }

    public function test_an_order_can_be_placed_without_a_retailer(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', ['dealer_id' => $dealer->id, 'retailer_id' => null]);
    }

    public function test_the_territory_filter_only_returns_orders_for_dealers_in_that_territory(): void
    {
        $admin = $this->superAdmin();
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        Order::factory()->create(['dealer_id' => $dealerA->id]);
        Order::factory()->create(['dealer_id' => $dealerB->id]);

        $response = $this->actingAs($admin)->get(route('orders.index', ['territory_id' => $territoryA->id]));

        $response->assertOk();
        $response->assertSee($dealerA->name);
        $response->assertDontSee($dealerB->name);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 500],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.discount_amount');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_at_least_one_item_is_required(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_super_admin_can_view_an_order_detail_page(): void
    {
        $admin = $this->superAdmin();
        $product = Product::factory()->create();
        $order = $this->orderWithItem($product);

        $this->actingAs($admin)->get(route('orders.show', $order))->assertOk();
    }

    public function test_super_admin_can_update_an_order_and_replace_its_items(): void
    {
        $admin = $this->superAdmin();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $order = $this->orderWithItem($productA, quantity: 5, unitPrice: 100);

        $this->actingAs($admin)->put(route('orders.update', $order), [
            'user_id' => $order->user_id,
            'dealer_id' => $order->dealer_id,
            'order_date' => $order->order_date->toDateString(),
            'items' => [
                ['product_id' => $productB->id, 'quantity' => 8, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ])->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'total_amount' => 800]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $productB->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id, 'product_id' => $productA->id]);
    }

    public function test_super_admin_can_delete_and_restore_an_order(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create();

        $this->actingAs($admin)->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'));
        $this->assertSoftDeleted('orders', ['id' => $order->id]);

        $this->actingAs($admin)->post(route('orders.restore', $order->id))
            ->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'deleted_at' => null]);
    }

    public function test_order_form_product_options_are_filtered_to_the_viewers_sales_team(): void
    {
        $teamA = SalesTeam::factory()->create();
        $teamB = SalesTeam::factory()->create();

        $ownTeamProduct = Product::factory()->create(['sales_team_id' => $teamA->id, 'name' => 'Own Team Product']);
        $otherTeamProduct = Product::factory()->create(['sales_team_id' => $teamB->id, 'name' => 'Other Team Product']);
        $teamLessProduct = Product::factory()->create(['sales_team_id' => null, 'name' => 'Team Less Product']);

        $admin = User::factory()->create(['sales_team_id' => $teamA->id]);
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)->get(route('orders.create'));

        $response->assertOk()
            ->assertSee($ownTeamProduct->name)
            ->assertSee($teamLessProduct->name)
            ->assertDontSee($otherTeamProduct->name);
    }

    public function test_editing_an_order_keeps_its_existing_product_even_if_outside_the_viewers_team(): void
    {
        $teamA = SalesTeam::factory()->create();
        $teamB = SalesTeam::factory()->create();

        $otherTeamProduct = Product::factory()->create(['sales_team_id' => $teamB->id, 'name' => 'Other Team Product']);
        $order = $this->orderWithItem($otherTeamProduct);

        $admin = User::factory()->create(['sales_team_id' => $teamA->id]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)->get(route('orders.edit', $order))
            ->assertOk()
            ->assertSee($otherTeamProduct->name);
    }

    public function test_a_new_order_starts_pending(): void
    {
        $admin = $this->superAdmin();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $this->actingAs($admin)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ]);

        $this->assertDatabaseHas('orders', ['dealer_id' => $dealer->id, 'status' => 'pending']);
    }

    public function test_super_admin_can_approve_a_pending_order(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('orders.approve', $order))->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'approved', 'approved_by' => $admin->id]);
    }

    public function test_super_admin_can_reject_a_pending_order(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch(route('orders.reject', $order))->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'rejected', 'approved_by' => $admin->id]);
    }

    public function test_an_already_approved_order_cannot_be_approved_again(): void
    {
        $admin = $this->superAdmin();
        $order = Order::factory()->create(['status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()]);

        $this->actingAs($admin)->patch(route('orders.approve', $order))->assertSessionHasErrors('status');
    }

    public function test_a_territory_manager_cannot_approve_an_order(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($manager)->patch(route('orders.approve', $order))->assertForbidden();
    }

    public function test_the_approval_filter_only_returns_orders_with_that_status(): void
    {
        $admin = $this->superAdmin();
        $pending = Order::factory()->create(['status' => 'pending']);
        $approved = Order::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($admin)->get(route('orders.index', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee($approved->dealer->name);
        $response->assertDontSee($pending->dealer->name);
    }
}
