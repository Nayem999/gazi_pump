<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SalesEntryTest extends TestCase
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

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/sales-entries', [])->assertStatus(401);
    }

    public function test_sales_executive_can_record_a_sale_with_multiple_products_at_current_prices(): void
    {
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $productA = Product::factory()->create(['price' => 250]);
        $productB = Product::factory()->create(['price' => 40]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-entries', [
                'customer_id' => $customer->id,
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 4],
                    ['product_id' => $productB->id, 'quantity' => 2],
                ],
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'data.items');

        $entry = SalesEntry::where('user_id', $executive->id)->firstOrFail();
        $this->assertCount(2, $entry->items);
        $this->assertSame('1080.00', (string) $entry->total_amount);
    }

    public function test_client_supplied_price_is_ignored_in_favor_of_the_products_current_price(): void
    {
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 250]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-entries', [
                'customer_id' => $customer->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1], // not a validated field — must have no effect
                ],
            ])->assertStatus(201);

        $entry = SalesEntry::where('user_id', $executive->id)->firstOrFail();
        $this->assertSame('250.00', (string) $entry->items->first()->unit_price);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.sales.max_discount_percent' => 20]);
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-entries', [
                'customer_id' => $customer->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 10, 'discount_amount' => 500],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_at_least_one_item_is_required(): void
    {
        $executive = $this->executive();
        $customer = Customer::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-entries', [
                'customer_id' => $customer->id,
                'items' => [],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_index_only_returns_the_authenticated_users_own_sales(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        SalesEntry::factory()->create(['user_id' => $executive->id, 'sale_date' => Carbon::yesterday()->toDateString()]);
        SalesEntry::factory()->create(['user_id' => $otherExecutive->id, 'sale_date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/sales-entries')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
