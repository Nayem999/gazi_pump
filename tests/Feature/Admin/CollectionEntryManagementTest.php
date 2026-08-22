<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CollectionEntryManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('collection-entries.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_collection_entries(): void
    {
        $this->actingAs($this->executive())->get(route('collection-entries.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_record_a_collection(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->actingAs($manager)->get(route('collection-entries.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('collection-entries.index'));
        $this->assertDatabaseHas('collection_entries', [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'amount' => 600,
        ]);
    }

    public function test_a_collection_beyond_the_overpayment_tolerance_is_rejected(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $response = $this->actingAs($manager)->post(route('collection-entries.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => Carbon::today()->toDateString(),
            'amount' => 2000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('collection_entries', 0);
    }

    public function test_general_manager_can_update_a_collection_entry(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);
        $collectionEntry = CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 300]);

        $this->actingAs($manager)->put(route('collection-entries.update', $collectionEntry), [
            'user_id' => $collectionEntry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => $collectionEntry->collection_date->toDateString(),
            'amount' => 500,
            'payment_method' => 'cheque',
            'reference_no' => 'CHK-1001',
        ])->assertRedirect(route('collection-entries.index'));

        $this->assertDatabaseHas('collection_entries', ['id' => $collectionEntry->id, 'amount' => 500, 'reference_no' => 'CHK-1001']);
    }

    public function test_general_manager_cannot_delete_a_collection_entry(): void
    {
        $manager = $this->generalManager();
        $collectionEntry = CollectionEntry::factory()->create();

        $this->actingAs($manager)->delete(route('collection-entries.destroy', $collectionEntry))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_collection_entry(): void
    {
        $admin = $this->superAdmin();
        $collectionEntry = CollectionEntry::factory()->create();

        $this->actingAs($admin)->delete(route('collection-entries.destroy', $collectionEntry))
            ->assertRedirect(route('collection-entries.index'));
        $this->assertSoftDeleted('collection_entries', ['id' => $collectionEntry->id]);

        $this->actingAs($admin)->post(route('collection-entries.restore', $collectionEntry->id))
            ->assertRedirect(route('collection-entries.index'));
        $this->assertDatabaseHas('collection_entries', ['id' => $collectionEntry->id, 'deleted_at' => null]);
    }

    public function test_territory_manager_can_view_but_not_create_collection_entries(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');

        $this->actingAs($manager)->get(route('collection-entries.index'))->assertOk();
        $this->actingAs($manager)->get(route('collection-entries.create'))->assertForbidden();
    }
}
