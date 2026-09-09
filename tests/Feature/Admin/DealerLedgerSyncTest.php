<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Dealer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealerLedgerSyncTest extends TestCase
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

    public function test_a_general_manager_can_trigger_a_ledger_sync_for_a_mapped_dealer(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);

        $this->actingAs($manager)->post(route('tally-integration.sync-ledger', $dealer))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sync_queues', ['entity_type' => 'ledger', 'entity_id' => $dealer->id]);
    }

    public function test_triggering_a_sync_for_an_unmapped_dealer_fails_validation(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create(['tally_guid' => null]);

        $this->actingAs($manager)->post(route('tally-integration.sync-ledger', $dealer))
            ->assertSessionHasErrors('dealer');

        $this->assertDatabaseMissing('sync_queues', ['entity_type' => 'ledger', 'entity_id' => $dealer->id]);
    }

    public function test_a_sales_executive_cannot_trigger_a_ledger_sync(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create(['tally_guid' => 'DEALER-GUID-1']);

        $this->actingAs($executive)->post(route('tally-integration.sync-ledger', $dealer))->assertForbidden();
    }
}
