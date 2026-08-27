<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Target;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetManagementTest extends TestCase
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

    private function territoryManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Territory Manager');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('targets.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_targets(): void
    {
        $this->actingAs($this->executive())->get(route('targets.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_assign_a_target(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();

        $this->actingAs($manager)->get(route('targets.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'order_value_target' => 100000,
            'collection_target' => 50000,
            'quantity_target' => 100,
        ]);

        $response->assertRedirect(route('targets.index'));
        $this->assertDatabaseHas('targets', ['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $target = Target::where('user_id', $executive->id)->firstOrFail();
        $this->assertNotNull($target->achievement);
    }

    public function test_a_duplicate_target_for_the_same_executive_and_period_is_rejected(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $response = $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'order_value_target' => 100000,
            'collection_target' => 50000,
            'quantity_target' => 100,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('targets', 1);
    }

    public function test_general_manager_can_view_a_target_detail_page(): void
    {
        $manager = $this->generalManager();
        $target = Target::factory()->create();

        $this->actingAs($manager)->get(route('targets.show', $target))->assertOk();
    }

    public function test_general_manager_can_update_a_target_and_it_recalculates(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $target = Target::factory()->create(['user_id' => $executive->id, 'order_value_target' => 1000]);
        Order::factory()->create(['user_id' => $executive->id, 'order_date' => now()->toDateString(), 'total_amount' => 1000]);

        $this->actingAs($manager)->put(route('targets.update', $target), [
            'user_id' => $executive->id,
            'month' => $target->month,
            'year' => $target->year,
            'order_value_target' => 2000,
            'collection_target' => (string) $target->collection_target,
            'quantity_target' => $target->quantity_target,
        ])->assertRedirect(route('targets.index'));

        $this->assertDatabaseHas('targets', ['id' => $target->id, 'order_value_target' => 2000]);
        $target->refresh();
        $this->assertNotNull($target->achievement);
    }

    public function test_recalculate_action_refreshes_the_achievement(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $target = Target::factory()->create(['user_id' => $executive->id, 'order_value_target' => 1000]);

        $this->actingAs($manager)->post(route('targets.recalculate', $target))
            ->assertRedirect();

        $this->assertDatabaseHas('achievements', ['target_id' => $target->id, 'order_achieved' => 0]);
    }

    public function test_general_manager_cannot_delete_a_target(): void
    {
        $manager = $this->generalManager();
        $target = Target::factory()->create();

        $this->actingAs($manager)->delete(route('targets.destroy', $target))->assertForbidden();
    }

    public function test_a_product_wise_target_sums_into_the_overall_target_fields(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $response = $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'mode' => 'product_wise',
            'product_targets' => [
                ['product_id' => $productA->id, 'order_target' => 60000, 'collection_target' => 30000, 'quantity_target' => 60],
                ['product_id' => $productB->id, 'order_target' => 40000, 'collection_target' => 20000, 'quantity_target' => 40],
            ],
        ]);

        $response->assertRedirect(route('targets.index'));

        $target = Target::where('user_id', $executive->id)->firstOrFail();
        $this->assertSame(100000.0, (float) $target->order_value_target);
        $this->assertSame(50000.0, (float) $target->collection_target);
        $this->assertSame(100, $target->quantity_target);
        $this->assertCount(2, $target->items);
        $this->assertTrue($target->isProductWise());
    }

    public function test_a_product_wise_target_requires_at_least_one_product_row(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();

        $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'mode' => 'product_wise',
        ])->assertSessionHasErrors('product_targets');
    }

    public function test_the_same_product_cannot_appear_twice_in_a_product_wise_target(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $product = Product::factory()->create();

        $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'mode' => 'product_wise',
            'product_targets' => [
                ['product_id' => $product->id, 'order_target' => 10000, 'collection_target' => 5000, 'quantity_target' => 10],
                ['product_id' => $product->id, 'order_target' => 20000, 'collection_target' => 10000, 'quantity_target' => 20],
            ],
        ])->assertSessionHasErrors('product_targets.0.product_id');
    }

    public function test_switching_a_target_from_product_wise_back_to_single_clears_the_breakdown(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $product = Product::factory()->create();
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        $target->items()->create(['product_id' => $product->id, 'order_target' => 1000, 'collection_target' => 500, 'quantity_target' => 10]);

        $this->actingAs($manager)->put(route('targets.update', $target), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'mode' => 'single',
            'order_value_target' => 5000,
            'collection_target' => 2500,
            'quantity_target' => 50,
        ])->assertRedirect(route('targets.index'));

        $target->refresh();
        $this->assertCount(0, $target->items);
        $this->assertFalse($target->isProductWise());
        $this->assertSame(5000.0, (float) $target->order_value_target);
    }

    public function test_the_detail_page_shows_the_product_wise_achievement_breakdown(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $product = Product::factory()->create(['name' => 'Gazi Test Pump']);

        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);
        $target->items()->create(['product_id' => $product->id, 'order_target' => 1000, 'collection_target' => 500, 'quantity_target' => 10]);

        $order = Order::factory()->create(['user_id' => $executive->id, 'order_date' => '2026-08-10', 'total_amount' => 400]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 4, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 400]);

        $response = $this->actingAs($manager)->get(route('targets.show', $target));

        $response->assertOk()
            ->assertSee('Product-wise Achievement Breakdown')
            ->assertSee('Gazi Test Pump')
            ->assertSee('400.00')
            ->assertSee('40.0%');
    }

    public function test_territory_manager_can_assign_a_target_to_their_team(): void
    {
        $manager = $this->territoryManager();
        $executive = $this->executive();

        $this->actingAs($manager)->get(route('targets.index'))->assertOk();

        $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 9,
            'year' => 2026,
            'order_value_target' => 50000,
            'collection_target' => 20000,
            'quantity_target' => 50,
        ])->assertRedirect(route('targets.index'));

        $this->assertDatabaseHas('targets', ['user_id' => $executive->id, 'month' => 9, 'year' => 2026]);
    }
}
