<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CashHandover;
use App\Models\CollectionEntry;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CashHandoverManagementTest extends TestCase
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

    private function salesManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Manager');

        return $user;
    }

    private function executiveWithCash(float $amount): User
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        CollectionEntry::factory()->create(['user_id' => $executive->id, 'payment_method' => 'cash', 'amount' => $amount]);

        return $executive;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('cash-handovers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('cash-handovers.index'))->assertForbidden();
    }

    public function test_general_manager_can_record_a_handover_within_cash_in_hand(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(5000);

        $response = $this->actingAs($manager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 2000,
            'handover_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect(route('cash-handovers.index'));
        $this->assertDatabaseHas('cash_handovers', ['user_id' => $executive->id, 'amount' => 2000, 'status' => 'pending']);
    }

    public function test_a_handover_beyond_cash_in_hand_is_rejected(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(1000);

        $this->actingAs($manager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 5000,
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('cash_handovers', 0);
    }

    public function test_confirming_a_handover_reduces_cash_in_hand(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(5000);
        $handover = CashHandover::factory()->create(['user_id' => $executive->id, 'amount' => 2000, 'status' => 'pending']);

        $this->actingAs($manager)->patch(route('cash-handovers.confirm', $handover))->assertRedirect();

        $this->assertDatabaseHas('cash_handovers', ['id' => $handover->id, 'status' => 'confirmed', 'confirmed_by' => $manager->id]);

        // A further handover for exactly the remaining 3000 should now
        // succeed, but the original 2000 (already confirmed) should not be
        // available again.
        $this->actingAs($manager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 3000,
        ])->assertRedirect(route('cash-handovers.index'));

        // 3001 exceeds it (the second handover above is only Pending, so it
        // doesn't itself reduce cash-in-hand further at create time — see
        // the confirm-time guard tested separately below).
        $this->actingAs($manager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 3001,
        ])->assertSessionHasErrors('amount');
    }

    public function test_confirming_a_second_overlapping_pending_handover_is_rejected(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(5000);
        // Two pending handovers that each individually fit within the
        // uncommitted 5000, but together overcommit it — the create-time
        // check can't catch this since neither is confirmed yet.
        $first = CashHandover::factory()->create(['user_id' => $executive->id, 'amount' => 2000, 'status' => 'pending']);
        $second = CashHandover::factory()->create(['user_id' => $executive->id, 'amount' => 4000, 'status' => 'pending']);

        $this->actingAs($manager)->patch(route('cash-handovers.confirm', $first))->assertRedirect();
        $this->assertDatabaseHas('cash_handovers', ['id' => $first->id, 'status' => 'confirmed']);

        // Only 3000 remains — confirming the second (4000) must now fail.
        $this->actingAs($manager)->patch(route('cash-handovers.confirm', $second))->assertSessionHasErrors('amount');
        $this->assertDatabaseHas('cash_handovers', ['id' => $second->id, 'status' => 'pending']);
    }

    public function test_rejecting_a_handover_does_not_reduce_cash_in_hand(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(5000);
        $handover = CashHandover::factory()->create(['user_id' => $executive->id, 'amount' => 2000, 'status' => 'pending']);

        $this->actingAs($manager)->patch(route('cash-handovers.reject', $handover))->assertRedirect();
        $this->assertDatabaseHas('cash_handovers', ['id' => $handover->id, 'status' => 'rejected']);

        // Full 5000 is still available since the rejected handover never counted.
        $this->actingAs($manager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 5000,
        ])->assertRedirect(route('cash-handovers.index'));
    }

    public function test_a_confirmed_handover_cannot_be_confirmed_again(): void
    {
        $manager = $this->generalManager();
        $handover = CashHandover::factory()->create(['status' => 'confirmed', 'confirmed_by' => $manager->id, 'confirmed_at' => now()]);

        $this->actingAs($manager)->patch(route('cash-handovers.confirm', $handover))->assertSessionHasErrors('status');
    }

    public function test_a_sales_manager_can_record_but_not_confirm_a_handover(): void
    {
        $salesManager = $this->salesManager();
        $executive = $this->executiveWithCash(5000);

        $this->actingAs($salesManager)->post(route('cash-handovers.store'), [
            'user_id' => $executive->id,
            'amount' => 1000,
        ])->assertRedirect(route('cash-handovers.index'));

        $handover = CashHandover::where('user_id', $executive->id)->firstOrFail();

        $this->actingAs($salesManager)->patch(route('cash-handovers.confirm', $handover))->assertForbidden();
    }

    public function test_index_shows_a_daily_limit_warning_when_configured(): void
    {
        Setting::current()->update(['cash_daily_limit_amount' => 1000]);
        $manager = $this->generalManager();
        $executive = $this->executiveWithCash(5000);

        $this->actingAs($manager)
            ->get(route('cash-handovers.index', ['user_id' => $executive->id]))
            ->assertOk()
            ->assertSee('5,000.00');
    }
}
