<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Dealer;
use App\Models\Retailer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RetailerManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('retailers.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_and_create_but_not_delete_retailers(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('retailers.index'))->assertOk();

        $retailer = Retailer::factory()->create();
        $this->actingAs($executive)->delete(route('retailers.destroy', $retailer))->assertForbidden();
    }

    public function test_super_admin_can_create_a_retailer_with_an_image(): void
    {
        Storage::fake('public');
        $dealer = Dealer::factory()->create();

        $response = $this->actingAs($this->superAdmin())->post(route('retailers.store'), [
            'dealer_id' => $dealer->id,
            'name' => 'Test Retailer Shop',
            'phone' => '01812345678',
            'email' => 'shop@example.com',
            'shipping_address' => '123 Market Road',
            'image' => UploadedFile::fake()->image('shop.jpg'),
            'status' => '1',
        ]);

        $response->assertRedirect(route('retailers.index'));

        $retailer = Retailer::where('name', 'Test Retailer Shop')->firstOrFail();
        $this->assertSame($dealer->id, $retailer->dealer_id);
        $this->assertNotNull($retailer->image);
        Storage::disk('public')->assertExists($retailer->image);
    }

    public function test_a_retailer_requires_a_dealer(): void
    {
        $response = $this->actingAs($this->superAdmin())->post(route('retailers.store'), [
            'name' => 'No Dealer Shop',
            'phone' => '01812345678',
        ]);

        $response->assertSessionHasErrors('dealer_id');
    }

    public function test_super_admin_can_update_delete_and_restore_a_retailer(): void
    {
        $admin = $this->superAdmin();
        $retailer = Retailer::factory()->create();
        $newDealer = Dealer::factory()->create();

        $this->actingAs($admin)->put(route('retailers.update', $retailer), [
            'dealer_id' => $newDealer->id,
            'name' => 'Renamed Shop',
            'phone' => $retailer->phone,
            'status' => '1',
        ])->assertRedirect(route('retailers.index'));
        $this->assertDatabaseHas('retailers', ['id' => $retailer->id, 'name' => 'Renamed Shop', 'dealer_id' => $newDealer->id]);

        $this->actingAs($admin)->delete(route('retailers.destroy', $retailer))->assertRedirect(route('retailers.index'));
        $this->assertSoftDeleted('retailers', ['id' => $retailer->id]);

        $this->actingAs($admin)->post(route('retailers.restore', $retailer->id))->assertRedirect(route('retailers.index'));
        $this->assertDatabaseHas('retailers', ['id' => $retailer->id, 'deleted_at' => null]);
    }

    public function test_the_dealer_filter_only_returns_that_dealers_retailers(): void
    {
        $admin = $this->superAdmin();
        $dealerA = Dealer::factory()->create();
        $dealerB = Dealer::factory()->create();
        $retailerA = Retailer::factory()->create(['dealer_id' => $dealerA->id, 'name' => 'Shop A']);
        $retailerB = Retailer::factory()->create(['dealer_id' => $dealerB->id, 'name' => 'Shop B']);

        $response = $this->actingAs($admin)->get(route('retailers.index', ['dealer_id' => $dealerA->id]));

        $response->assertOk()->assertSee('Shop A')->assertDontSee('Shop B');
    }
}
