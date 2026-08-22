<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Target;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetManagementTest extends TestCase
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

    private function territoryManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Territory Manager');

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
        $this->get(route('targets.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_targets(): void
    {
        $this->actingAs($this->executive())->get(route('targets.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_assign_a_target(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();

        $this->actingAs($manager)->get(route('targets.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'order_value_target' => 100000,
            'collection_target' => 50000,
            'quantity_target' => 100,
        ]);

        $response->assertRedirect(route('targets.index'));
        $this->assertDatabaseHas('targets', ['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $target = Target::where('user_id', $executive->id)->firstOrFail();
        $this->assertNotNull($target->achievement);
    }

    public function test_a_duplicate_target_for_the_same_executive_and_period_is_rejected(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        Target::factory()->create(['user_id' => $executive->id, 'month' => 8, 'year' => 2026]);

        $response = $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
            'order_value_target' => 100000,
            'collection_target' => 50000,
            'quantity_target' => 100,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('targets', 1);
    }

    public function test_general_manager_can_view_a_target_detail_page(): void
    {
        $manager = $this->generalManager();
        $target = Target::factory()->create();

        $this->actingAs($manager)->get(route('targets.show', $target))->assertOk();
    }

    public function test_general_manager_can_update_a_target_and_it_recalculates(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $target = Target::factory()->create(['user_id' => $executive->id, 'order_value_target' => 1000]);
        Order::factory()->create(['user_id' => $executive->id, 'order_date' => now()->toDateString(), 'total_amount' => 1000]);

        $this->actingAs($manager)->put(route('targets.update', $target), [
            'user_id' => $executive->id,
            'month' => $target->month,
            'year' => $target->year,
            'order_value_target' => 2000,
            'collection_target' => (string) $target->collection_target,
            'quantity_target' => $target->quantity_target,
        ])->assertRedirect(route('targets.index'));

        $this->assertDatabaseHas('targets', ['id' => $target->id, 'order_value_target' => 2000]);
        $target->refresh();
        $this->assertNotNull($target->achievement);
    }

    public function test_recalculate_action_refreshes_the_achievement(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $target = Target::factory()->create(['user_id' => $executive->id, 'order_value_target' => 1000]);

        $this->actingAs($manager)->post(route('targets.recalculate', $target))
            ->assertRedirect();

        $this->assertDatabaseHas('achievements', ['target_id' => $target->id, 'order_achieved' => 0]);
    }

    public function test_general_manager_cannot_delete_a_target(): void
    {
        $manager = $this->generalManager();
        $target = Target::factory()->create();

        $this->actingAs($manager)->delete(route('targets.destroy', $target))->assertForbidden();
    }

    public function test_territory_manager_can_assign_a_target_to_their_team(): void
    {
        $manager = $this->territoryManager();
        $executive = $this->executive();

        $this->actingAs($manager)->get(route('targets.index'))->assertOk();

        $this->actingAs($manager)->post(route('targets.store'), [
            'user_id' => $executive->id,
            'month' => 9,
            'year' => 2026,
            'order_value_target' => 50000,
            'collection_target' => 20000,
            'quantity_target' => 50,
        ])->assertRedirect(route('targets.index'));

        $this->assertDatabaseHas('targets', ['user_id' => $executive->id, 'month' => 9, 'year' => 2026]);
    }
}
