<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesEntryManagementTest extends TestCase
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

    private function salesEntryWithItem(Product $product, int $quantity = 5, float $unitPrice = 100): SalesEntry
    {
        $entry = SalesEntry::factory()->create(['total_amount' => $quantity * $unitPrice]);
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
        $this->get(route('sales-entries.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_sales_entries(): void
    {
        $this->actingAs($this->executive())->get(route('sales-entries.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_record_a_sale_with_multiple_products(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $productA = Product::factory()->create(['price' => 100]);
        $productB = Product::factory()->create(['price' => 50]);

        $this->actingAs($manager)->get(route('sales-entries.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('sales-entries.store'), [
            'user_id' => $executive->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 50],
                ['product_id' => $productB->id, 'quantity' => 2, 'unit_price' => 50, 'discount_amount' => 0],
            ],
        ]);

        $response->assertRedirect(route('sales-entries.index'));
        $this->assertDatabaseHas('sales_entries', [
            'user_id' => $executive->id,
            'customer_id' => $customer->id,
            'total_amount' => 550,
        ]);
        $this->assertDatabaseCount('sales_entry_items', 2);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.sales.max_discount_percent' => 20]);
        $manager = $this->generalManager();
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($manager)->post(route('sales-entries.store'), [
            'user_id' => $executive->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::today()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 500],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.discount_amount');
        $this->assertDatabaseCount('sales_entries', 0);
    }

    public function test_at_least_one_item_is_required(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($manager)->post(route('sales-entries.store'), [
            'user_id' => $executive->id,
            'customer_id' => $customer->id,
            'sale_date' => Carbon::today()->toDateString(),
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_general_manager_can_view_a_sale_detail_page(): void
    {
        $manager = $this->generalManager();
        $product = Product::factory()->create();
        $salesEntry = $this->salesEntryWithItem($product);

        $this->actingAs($manager)->get(route('sales-entries.show', $salesEntry))->assertOk();
    }

    public function test_general_manager_can_update_a_sales_entry_and_replace_its_items(): void
    {
        $manager = $this->generalManager();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $salesEntry = $this->salesEntryWithItem($productA, quantity: 5, unitPrice: 100);

        $this->actingAs($manager)->put(route('sales-entries.update', $salesEntry), [
            'user_id' => $salesEntry->user_id,
            'customer_id' => $salesEntry->customer_id,
            'sale_date' => $salesEntry->sale_date->toDateString(),
            'items' => [
                ['product_id' => $productB->id, 'quantity' => 8, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ])->assertRedirect(route('sales-entries.index'));

        $this->assertDatabaseHas('sales_entries', ['id' => $salesEntry->id, 'total_amount' => 800]);
        $this->assertDatabaseHas('sales_entry_items', ['sales_entry_id' => $salesEntry->id, 'product_id' => $productB->id]);
        $this->assertDatabaseMissing('sales_entry_items', ['sales_entry_id' => $salesEntry->id, 'product_id' => $productA->id]);
    }

    public function test_general_manager_cannot_delete_a_sales_entry(): void
    {
        $manager = $this->generalManager();
        $salesEntry = SalesEntry::factory()->create();

        $this->actingAs($manager)->delete(route('sales-entries.destroy', $salesEntry))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_sales_entry(): void
    {
        $admin = $this->superAdmin();
        $salesEntry = SalesEntry::factory()->create();

        $this->actingAs($admin)->delete(route('sales-entries.destroy', $salesEntry))
            ->assertRedirect(route('sales-entries.index'));
        $this->assertSoftDeleted('sales_entries', ['id' => $salesEntry->id]);

        $this->actingAs($admin)->post(route('sales-entries.restore', $salesEntry->id))
            ->assertRedirect(route('sales-entries.index'));
        $this->assertDatabaseHas('sales_entries', ['id' => $salesEntry->id, 'deleted_at' => null]);
    }

    public function test_territory_manager_can_view_but_not_create_sales_entries(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');

        $this->actingAs($manager)->get(route('sales-entries.index'))->assertOk();
        $this->actingAs($manager)->get(route('sales-entries.create'))->assertForbidden();
    }
}
