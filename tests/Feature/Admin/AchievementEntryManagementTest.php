<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AchievementEntry;
use App\Models\Product;
use App\Models\SalesTeam;
use App\Models\Target;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementEntryManagementTest extends TestCase
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
        $this->get(route('achievements.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_only_their_own_achievements(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();
        $own = AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => now()->toDateString()]);
        $other = AchievementEntry::factory()->create(['user_id' => $otherExecutive->id, 'entry_date' => now()->toDateString()]);

        $this->actingAs($executive)->get(route('achievements.create'))->assertOk();

        $response = $this->actingAs($executive)->get(route('achievements.index'));
        $response->assertOk()->assertSee($executive->name)->assertDontSee($otherExecutive->name);

        $this->actingAs($executive)->get(route('achievements.show', $other))->assertForbidden();
        $this->actingAs($executive)->get(route('achievements.show', $own))->assertOk();
    }

    public function test_the_create_form_locks_a_plain_sales_executive_to_themself(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        $response = $this->actingAs($executive)->get(route('achievements.create'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/<select[^>]*name="user_id"[^>]*disabled[^>]*>/', $html);
        $this->assertStringContainsString((string) $executive->id, $html);

        // Only the executive themself is offered as an option — not every
        // Sales Executive in the company.
        $response->assertSee($executive->name)->assertDontSee($otherExecutive->name);
    }

    public function test_a_plain_sales_executive_cannot_record_an_achievement_for_someone_else_even_by_tampering_the_request(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        $response = $this->actingAs($executive)->post(route('achievements.store'), [
            'user_id' => $otherExecutive->id,
            'entry_date' => now()->toDateString(),
            'mode' => 'single',
            'order_value_achieved' => 500,
            'collection_achieved' => 200,
            'quantity_achieved' => 5,
        ]);

        $response->assertRedirect(route('achievements.index'));
        $this->assertDatabaseHas('achievement_entries', ['user_id' => $executive->id, 'order_value_achieved' => 500]);
        $this->assertDatabaseMissing('achievement_entries', ['user_id' => $otherExecutive->id]);
    }

    public function test_a_territory_managers_index_is_scoped_to_their_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveA = User::factory()->inTerritory($territoryA)->create();
        $executiveB = User::factory()->inTerritory($territoryB)->create();
        AchievementEntry::factory()->create(['user_id' => $executiveA->id, 'entry_date' => now()->toDateString()]);
        AchievementEntry::factory()->create(['user_id' => $executiveB->id, 'entry_date' => now()->toDateString()]);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->get(route('achievements.index'));

        $response->assertOk()->assertSee($executiveA->name)->assertDontSee($executiveB->name);
    }

    public function test_general_manager_can_record_a_single_achievement(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();

        $response = $this->actingAs($manager)->post(route('achievements.store'), [
            'user_id' => $executive->id,
            'entry_date' => now()->toDateString(),
            'mode' => 'single',
            'order_value_achieved' => 5000,
            'collection_achieved' => 2000,
            'quantity_achieved' => 20,
        ]);

        $response->assertRedirect(route('achievements.index'));
        $this->assertDatabaseHas('achievement_entries', [
            'user_id' => $executive->id,
            'order_value_achieved' => 5000,
            'status' => 'pending',
        ]);
    }

    public function test_a_duplicate_achievement_for_the_same_executive_and_date_is_rejected(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => '2026-08-15']);

        $response = $this->actingAs($manager)->post(route('achievements.store'), [
            'user_id' => $executive->id,
            'entry_date' => '2026-08-15',
            'mode' => 'single',
            'order_value_achieved' => 100,
            'collection_achieved' => 50,
            'quantity_achieved' => 1,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('achievement_entries', 1);
    }

    public function test_a_product_wise_achievement_sums_into_the_overall_fields(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $response = $this->actingAs($manager)->post(route('achievements.store'), [
            'user_id' => $executive->id,
            'entry_date' => now()->toDateString(),
            'mode' => 'product_wise',
            'achievement_items' => [
                ['product_id' => $productA->id, 'order_achieved' => 1000, 'collection_achieved' => 400, 'quantity_achieved' => 10],
                ['product_id' => $productB->id, 'order_achieved' => 2000, 'collection_achieved' => 600, 'quantity_achieved' => 20],
            ],
        ]);

        $response->assertRedirect(route('achievements.index'));
        $this->assertDatabaseHas('achievement_entries', [
            'user_id' => $executive->id,
            'order_value_achieved' => 3000,
            'collection_achieved' => 1000,
            'quantity_achieved' => 30,
        ]);
        $this->assertDatabaseCount('achievement_items', 2);
    }

    public function test_a_product_wise_achievement_requires_at_least_one_product_row(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();

        $response = $this->actingAs($manager)->post(route('achievements.store'), [
            'user_id' => $executive->id,
            'entry_date' => now()->toDateString(),
            'mode' => 'product_wise',
            'achievement_items' => [],
        ]);

        $response->assertSessionHasErrors('achievement_items');
    }

    public function test_switching_an_achievement_from_product_wise_back_to_single_clears_the_breakdown(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $product = Product::factory()->create();
        $entry = AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => now()->toDateString()]);
        $entry->items()->create(['product_id' => $product->id, 'order_achieved' => 500, 'collection_achieved' => 200, 'quantity_achieved' => 5]);

        $this->actingAs($manager)->put(route('achievements.update', $entry), [
            'user_id' => $executive->id,
            'entry_date' => $entry->entry_date->toDateString(),
            'mode' => 'single',
            'order_value_achieved' => 3000,
            'collection_achieved' => 1500,
            'quantity_achieved' => 30,
        ])->assertRedirect(route('achievements.index'));

        $entry->refresh();
        $this->assertCount(0, $entry->items);
        $this->assertFalse($entry->isProductWise());
        $this->assertSame(3000.0, (float) $entry->order_value_achieved);
    }

    public function test_general_manager_can_approve_a_pending_achievement(): void
    {
        $manager = $this->generalManager();
        $entry = AchievementEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($manager)->patch(route('achievements.approve', $entry))->assertRedirect();

        $this->assertDatabaseHas('achievement_entries', ['id' => $entry->id, 'status' => 'approved', 'approved_by' => $manager->id]);
    }

    public function test_general_manager_can_reject_a_pending_achievement(): void
    {
        $manager = $this->generalManager();
        $entry = AchievementEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($manager)->patch(route('achievements.reject', $entry))->assertRedirect();

        $this->assertDatabaseHas('achievement_entries', ['id' => $entry->id, 'status' => 'rejected', 'approved_by' => $manager->id]);
    }

    public function test_an_already_approved_achievement_cannot_be_approved_again(): void
    {
        $manager = $this->generalManager();
        $entry = AchievementEntry::factory()->create(['status' => 'approved', 'approved_by' => $manager->id, 'approved_at' => now()]);

        $this->actingAs($manager)->patch(route('achievements.approve', $entry))->assertSessionHasErrors('status');
    }

    public function test_a_territory_manager_cannot_approve_an_achievement(): void
    {
        $manager = $this->territoryManager();
        $entry = AchievementEntry::factory()->create(['status' => 'pending']);

        $this->actingAs($manager)->patch(route('achievements.approve', $entry))->assertForbidden();
    }

    public function test_a_sales_executive_cannot_approve_their_own_achievement(): void
    {
        $executive = $this->executive();
        $entry = AchievementEntry::factory()->create(['user_id' => $executive->id, 'status' => 'pending']);

        $this->actingAs($executive)->patch(route('achievements.approve', $entry))->assertForbidden();
    }

    public function test_approving_an_achievement_recalculates_its_linked_target(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $today = now();
        Target::factory()->create([
            'user_id' => $executive->id,
            'month' => $today->month,
            'year' => $today->year,
            'order_value_target' => 1000,
        ]);
        $entry = AchievementEntry::factory()->create([
            'user_id' => $executive->id,
            'entry_date' => $today->toDateString(),
            'order_value_achieved' => 500,
            'status' => 'pending',
        ]);

        $this->actingAs($manager)->patch(route('achievements.approve', $entry));

        $target = Target::where('user_id', $executive->id)->firstOrFail();
        $this->assertSame('50.00', (string) $target->achievement->order_pct);
    }

    public function test_general_manager_cannot_delete_an_achievement(): void
    {
        $manager = $this->generalManager();
        $entry = AchievementEntry::factory()->create();

        $this->actingAs($manager)->delete(route('achievements.destroy', $entry))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_an_achievement(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $entry = AchievementEntry::factory()->create();

        $this->actingAs($admin)->delete(route('achievements.destroy', $entry))
            ->assertRedirect(route('achievements.index'));
        $this->assertSoftDeleted('achievement_entries', ['id' => $entry->id]);

        $this->actingAs($admin)->post(route('achievements.restore', $entry->id))
            ->assertRedirect(route('achievements.index'));
        $this->assertDatabaseHas('achievement_entries', ['id' => $entry->id, 'deleted_at' => null]);
    }

    public function test_achievement_form_product_options_are_filtered_to_the_selected_executives_sales_team(): void
    {
        $manager = $this->generalManager();
        $teamA = SalesTeam::factory()->create();
        $ownTeamProduct = Product::factory()->create(['sales_team_id' => $teamA->id, 'name' => 'Own Team Product']);
        $teamLessProduct = Product::factory()->create(['sales_team_id' => null, 'name' => 'Team Less Product']);

        $response = $this->actingAs($manager)->get(route('achievements.create'));

        $response->assertOk()->assertSee($ownTeamProduct->name)->assertSee($teamLessProduct->name);
    }
}
