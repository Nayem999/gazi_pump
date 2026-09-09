<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Depot;
use App\Models\ProductStock;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('depots.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('depots.index'))->assertForbidden();
    }

    public function test_a_manager_can_view_but_not_create_a_depot(): void
    {
        $manager = $this->salesManager();

        $this->actingAs($manager)->get(route('depots.index'))->assertOk();

        $this->actingAs($manager)->post(route('depots.store'), [
            'name' => 'Blocked Depot',
            'code' => 'DEP-BLOCK',
            'status' => '1',
        ])->assertForbidden();
    }

    public function test_general_manager_can_create_and_update_a_depot(): void
    {
        $manager = $this->generalManager();

        $response = $this->actingAs($manager)->post(route('depots.store'), [
            'name' => 'Central Depot',
            'code' => 'DEP-CENTRAL',
            'address' => '123 Main Rd',
            'status' => '1',
        ]);

        $response->assertRedirect(route('depots.index'));
        $this->assertDatabaseHas('depots', ['code' => 'DEP-CENTRAL', 'name' => 'Central Depot']);

        $depot = Depot::where('code', 'DEP-CENTRAL')->firstOrFail();

        $this->actingAs($manager)->put(route('depots.update', $depot), [
            'name' => 'Renamed Depot',
            'code' => $depot->code,
            'status' => '1',
        ])->assertRedirect(route('depots.index'));
        $this->assertDatabaseHas('depots', ['id' => $depot->id, 'name' => 'Renamed Depot']);
    }

    public function test_super_admin_can_delete_and_restore_a_depot(): void
    {
        $admin = $this->superAdmin();
        $depot = Depot::factory()->create();

        $this->actingAs($admin)->delete(route('depots.destroy', $depot))->assertRedirect(route('depots.index'));
        $this->assertSoftDeleted('depots', ['id' => $depot->id]);

        $this->actingAs($admin)->post(route('depots.restore', $depot->id))->assertRedirect(route('depots.index'));
        $this->assertDatabaseHas('depots', ['id' => $depot->id, 'deleted_at' => null]);
    }

    public function test_code_must_be_unique(): void
    {
        Depot::factory()->create(['code' => 'DEP-1']);

        $this->actingAs($this->generalManager())->post(route('depots.store'), [
            'name' => 'Duplicate',
            'code' => 'DEP-1',
        ])->assertSessionHasErrors('code');
    }

    public function test_stock_page_shows_tally_owned_and_sfa_owned_columns_separately(): void
    {
        $stock = ProductStock::factory()->create([
            'closing_qty' => 100,
            'available_qty' => 80,
            'reserved_qty' => 20,
            'allocated_qty' => 20,
        ]);

        $response = $this->actingAs($this->generalManager())->get(route('depot-stock.index'));

        $response->assertOk();
        $response->assertSee($stock->depot->name);
        $response->assertSee($stock->product->name);
        // sellableQty() = available_qty - reserved_qty = 60
        $response->assertSee('60.00');
    }

    public function test_a_manager_can_view_stock_but_a_sales_executive_cannot(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($this->salesManager())->get(route('depot-stock.index'))->assertOk();
        $this->actingAs($executive)->get(route('depot-stock.index'))->assertForbidden();
    }
}
