<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\SalesEntryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.purchases.index'))->assertRedirect(route('portal.login'));
    }

    public function test_an_account_with_no_linked_customer_sees_an_empty_purchase_list(): void
    {
        $account = CustomerAccount::factory()->create(['email' => 'unlinked@example.com']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.index'))
            ->assertOk()
            ->assertSee('made any purchases yet');
    }

    public function test_a_linked_customer_sees_their_own_purchases_listed(): void
    {
        $customer = Customer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['customer_id' => $customer->id, 'email' => 'linked@example.com']);

        $salesEntry = SalesEntry::factory()->create(['customer_id' => $customer->id, 'total_amount' => 250]);
        SalesEntryItem::factory()->create(['sales_entry_id' => $salesEntry->id]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.index'))
            ->assertOk()
            ->assertSee(route('portal.purchases.show', $salesEntry), false)
            ->assertSee(number_format(250, 2));
    }

    public function test_a_customer_can_view_their_own_purchase_detail(): void
    {
        $customer = Customer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['customer_id' => $customer->id, 'email' => 'linked@example.com']);

        $product = Product::factory()->create(['name' => 'Fuel Pump XL']);
        $salesEntry = SalesEntry::factory()->create(['customer_id' => $customer->id, 'total_amount' => 500]);
        SalesEntryItem::factory()->create([
            'sales_entry_id' => $salesEntry->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 250,
            'total_amount' => 500,
        ]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.show', $salesEntry))
            ->assertOk()
            ->assertSee('Fuel Pump XL')
            ->assertSee(number_format(500, 2));
    }

    public function test_a_customer_cannot_view_another_customers_purchase(): void
    {
        $ownCustomer = Customer::factory()->create(['email' => 'me@example.com']);
        $account = CustomerAccount::factory()->create(['customer_id' => $ownCustomer->id, 'email' => 'me@example.com']);

        $otherCustomer = Customer::factory()->create();
        $othersSalesEntry = SalesEntry::factory()->create(['customer_id' => $otherCustomer->id]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.purchases.show', $othersSalesEntry))
            ->assertNotFound();
    }
}
