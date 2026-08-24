<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderManagementTest extends TestCase
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

    public function test_sales_executive_has_no_web_access_to_orders(): void
    {
        $this->actingAs($this->executive())->get(route('orders.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_record_an_order_with_multiple_products(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $productA = Product::factory()->create(['price' => 100]);
        $productB = Product::factory()->create(['price' => 50]);

        $this->actingAs($manager)->get(route('orders.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('orders.store'), [
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

    public function test_the_territory_filter_only_returns_orders_for_dealers_in_that_territory(): void
    {
        $manager = $this->generalManager();
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        Order::factory()->create(['dealer_id' => $dealerA->id]);
        Order::factory()->create(['dealer_id' => $dealerB->id]);

        $response = $this->actingAs($manager)->get(route('orders.index', ['territory_id' => $territoryA->id]));

        $response->assertOk();
        $response->assertSee($dealerA->name);
        $response->assertDontSee($dealerB->name);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($manager)->post(route('orders.store'), [
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
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($manager)->post(route('orders.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::today()->toDateString(),
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_general_manager_can_view_an_order_detail_page(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        $order = $this->orderWithItem($product);

        $this->actingAs($manager)->get(route('orders.show', $order))->assertOk();
    }

    public function test_general_manager_can_update_an_order_and_replace_its_items(): void
    {
        $manager = $this->generalManager();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $order = $this->orderWithItem($productA, quantity: 5, unitPrice: 100);

        $this->actingAs($manager)->put(route('orders.update', $order), [
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

    public function test_general_manager_cannot_delete_an_order(): void
    {
        $manager = $this->generalManager();
        $order = Order::factory()->create();

        $this->actingAs($manager)->delete(route('orders.destroy', $order))->assertForbidden();
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

    public function test_territory_manager_can_view_but_not_create_orders(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');

        $this->actingAs($manager)->get(route('orders.index'))->assertOk();
        $this->actingAs($manager)->get(route('orders.create'))->assertForbidden();
    }
}
