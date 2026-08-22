<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\CustomerAccount;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.purchases.index'))->assertRedirect(route('portal.login'));
    }

    public function test_an_account_with_no_linked_dealer_sees_an_empty_purchase_list(): void
    {
        $account = CustomerAccount::factory()->create(['email' => 'unlinked@example.com']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.index'))
            ->assertOk()
            ->assertSee('made any purchases yet');
    }

    public function test_a_linked_dealer_sees_their_own_purchases_listed(): void
    {
        $dealer = Dealer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => $dealer->id, 'email' => 'linked@example.com']);

        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 250]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.index'))
            ->assertOk()
            ->assertSee(route('portal.purchases.show', $order), false)
            ->assertSee(number_format(250, 2));
    }

    public function test_a_dealer_can_view_their_own_purchase_detail(): void
    {
        $dealer = Dealer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => $dealer->id, 'email' => 'linked@example.com']);

        $product = Product::factory()->create(['name' => 'Fuel Pump XL']);
        $order = Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 500]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 250,
            'total_amount' => 500,
        ]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.show', $order))
            ->assertOk()
            ->assertSee('Fuel Pump XL')
            ->assertSee(number_format(500, 2));
    }

    public function test_a_dealer_cannot_view_another_dealers_purchase(): void
    {
        $ownDealer = Dealer::factory()->create(['email' => 'me@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => $ownDealer->id, 'email' => 'me@example.com']);

        $otherDealer = Dealer::factory()->create();
        $othersOrder = Order::factory()->create(['dealer_id' => $otherDealer->id]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.show', $othersOrder))
            ->assertNotFound();
    }
}
