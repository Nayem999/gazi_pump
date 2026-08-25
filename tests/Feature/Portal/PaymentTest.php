<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\CollectionEntry;
use App\Models\CustomerAccount;
use App\Models\Dealer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('portal.payments.index'))->assertRedirect(route('portal.login'));
    }

    public function test_an_account_with_no_linked_dealer_sees_an_empty_payment_list(): void
    {
        $account = CustomerAccount::factory()->create(['email' => 'unlinked@example.com']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.payments.index'))
            ->assertOk()
            ->assertSee('made any payments yet');
    }

    public function test_a_linked_dealer_sees_their_own_payments_listed(): void
    {
        $dealer = Dealer::factory()->create(['email' => 'linked@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => $dealer->id, 'email' => 'linked@example.com']);

        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 300, 'payment_method' => 'cash']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.payments.index'))
            ->assertOk()
            ->assertSee(number_format(300, 2));
    }

    public function test_a_dealer_does_not_see_another_dealers_payments(): void
    {
        $ownDealer = Dealer::factory()->create(['email' => 'me@example.com']);
        $account = CustomerAccount::factory()->create(['dealer_id' => $ownDealer->id, 'email' => 'me@example.com']);

        $otherDealer = Dealer::factory()->create();
        CollectionEntry::factory()->create(['dealer_id' => $otherDealer->id, 'amount' => 999, 'payment_method' => 'cash']);

        $this->actingAs($account, 'customer')
            ->get(route('portal.payments.index'))
            ->assertOk()
            ->assertDontSee(number_format(999, 2));
    }
}
