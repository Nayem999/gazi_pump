<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReturnTest extends TestCase
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
        $this->postJson('/api/v1/sales-returns', [])->assertStatus(401);
    }

    public function test_sales_executive_can_request_a_return_against_their_own_order(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'user_id' => $executive->id, 'status' => 'approved']);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-returns', [
                'order_id' => $order->id,
                'reason' => 'Wrong item',
                'items' => [['order_item_id' => $item->id, 'quantity' => 2]],
            ]);

        $response->assertStatus(201)->assertJsonPath('data.status', 'requested');
        $this->assertSame(1, SalesReturn::count());
    }

    public function test_a_sales_executive_cannot_request_a_return_against_another_executives_order(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();
        $dealer = Dealer::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'user_id' => $otherExecutive->id, 'status' => 'approved']);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/sales-returns', [
                'order_id' => $order->id,
                'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
            ])->assertStatus(404);
    }

    public function test_index_only_returns_the_authenticated_users_own_returns(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        SalesReturn::factory()->create(['user_id' => $executive->id]);
        SalesReturn::factory()->create(['user_id' => $otherExecutive->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/sales-returns');

        $response->assertOk()->assertJsonCount(1, 'data');
    }
}
