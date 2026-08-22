<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\CustomerAccount;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('portal.login'));
    }

    public function test_account_sidebar_links_appear_on_the_dashboard_and_on_list_pages(): void
    {
        $account = CustomerAccount::factory()->create();

        $this->actingAs($account, 'customer')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee(route('portal.profile.edit'), false)
            ->assertSee(route('portal.inquiries.index'), false)
            ->assertSee(route('portal.visit-requests.index'), false);

        $this->actingAs($account, 'customer')
            ->get(route('portal.inquiries.index'))
            ->assertOk()
            ->assertSee(route('portal.dashboard'), false)
            ->assertSee(route('portal.visit-requests.index'), false);

        $this->actingAs($account, 'customer')
            ->get(route('portal.visit-requests.index'))
            ->assertOk()
            ->assertSee(route('portal.dashboard'), false)
            ->assertSee(route('portal.inquiries.index'), false);
    }

    public function test_an_account_with_no_matching_dealer_record_sees_a_zeroed_out_dashboard(): void
    {
        $account = CustomerAccount::factory()->create(['email' => 'unlinked@example.com']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('No purchase history yet.')
            ->assertSee('No product purchases yet.');
    }

    public function test_an_account_is_auto_linked_to_a_dealer_record_sharing_its_email_and_shows_purchase_history(): void
    {
        $dealer = Dealer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => null, 'email' => 'linked@example.com']);

        $product = Product::factory()->create(['name' => 'Fuel Pump XL']);
        $order = Order::factory()->create([
            'dealer_id' => $dealer->id,
            'order_date' => Carbon::now()->startOfMonth()->toDateString(),
            'total_amount' => 500,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 250,
            'discount_amount' => 0,
            'total_amount' => 500,
        ]);

        $this->actingAs($account, 'customer')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Fuel Pump XL');

        $this->assertSame($dealer->id, $account->fresh()->dealer_id);
    }
}
