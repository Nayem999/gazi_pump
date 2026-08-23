<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\VisitPlanStatus;
use App\Models\Dealer;
use App\Models\Territory;
use App\Models\User;
use App\Models\VisitPlan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VisitPlanManagementTest extends TestCase
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
        $this->get(route('visit-plans.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_visit_plans(): void
    {
        $this->actingAs($this->executive())->get(route('visit-plans.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_create_a_visit_plan(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $this->actingAs($manager)->get(route('visit-plans.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('visit-plans.store'), [
            'user_id' => $executive->id,
            'dealer_ids' => [$dealer->id],
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
            'notes' => 'Discuss new product line.',
        ]);

        $response->assertRedirect(route('visit-plans.index'));
        $this->assertDatabaseHas('visit_plans', [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'status' => 'planned',
        ]);
    }

    public function test_general_manager_can_create_visit_plans_for_multiple_dealers_at_once(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealers = Dealer::factory()->count(3)->create();

        $response = $this->actingAs($manager)->post(route('visit-plans.store'), [
            'user_id' => $executive->id,
            'dealer_ids' => $dealers->pluck('id')->all(),
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ]);

        $response->assertRedirect(route('visit-plans.index'));
        $this->assertSame(3, VisitPlan::where('user_id', $executive->id)->count());

        foreach ($dealers as $dealer) {
            $this->assertDatabaseHas('visit_plans', [
                'user_id' => $executive->id,
                'dealer_id' => $dealer->id,
                'status' => 'planned',
            ]);
        }
    }

    /**
     * A real browser form submits every field as a string (unlike this
     * test's other cases, which pass PHP ints straight through Laravel's
     * ->post() helper) — the territory resolution logic must tolerate that,
     * not just already-cast native types.
     */
    public function test_creating_a_visit_plan_with_string_typed_form_input_still_resolves_the_territory(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $territory = Territory::factory()->create();
        $dealer = Dealer::factory()->create(['territory_id' => $territory->id]);

        $this->actingAs($manager)->post(route('visit-plans.store'), [
            'user_id' => (string) $executive->id,
            'dealer_ids' => [(string) $dealer->id],
            'territory_id' => '',
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ])->assertRedirect(route('visit-plans.index'));

        $this->assertDatabaseHas('visit_plans', [
            'dealer_id' => $dealer->id,
            'territory_id' => $territory->id,
        ]);
    }

    public function test_creating_a_visit_plan_auto_fills_territory_from_the_dealer_when_none_is_chosen(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $territory = Territory::factory()->create();
        $dealer = Dealer::factory()->create(['territory_id' => $territory->id]);

        $this->actingAs($manager)->post(route('visit-plans.store'), [
            'user_id' => $executive->id,
            'dealer_ids' => [$dealer->id],
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ])->assertRedirect(route('visit-plans.index'));

        $this->assertDatabaseHas('visit_plans', [
            'dealer_id' => $dealer->id,
            'territory_id' => $territory->id,
        ]);
    }

    public function test_an_explicitly_chosen_territory_overrides_the_dealers_own_territory(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealersTerritory = Territory::factory()->create();
        $chosenTerritory = Territory::factory()->create();
        $dealer = Dealer::factory()->create(['territory_id' => $dealersTerritory->id]);

        $this->actingAs($manager)->post(route('visit-plans.store'), [
            'user_id' => $executive->id,
            'dealer_ids' => [$dealer->id],
            'territory_id' => $chosenTerritory->id,
            'planned_date' => Carbon::tomorrow()->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ])->assertRedirect(route('visit-plans.index'));

        $this->assertDatabaseHas('visit_plans', [
            'dealer_id' => $dealer->id,
            'territory_id' => $chosenTerritory->id,
        ]);
    }

    public function test_general_manager_can_update_a_visit_plan(): void
    {
        $manager = $this->generalManager();
        $visitPlan = VisitPlan::factory()->create();

        $this->actingAs($manager)->put(route('visit-plans.update', $visitPlan), [
            'user_id' => $visitPlan->user_id,
            'dealer_id' => $visitPlan->dealer_id,
            'planned_date' => $visitPlan->planned_date->toDateString(),
            'status' => VisitPlanStatus::Cancelled->value,
            'notes' => 'Dealer requested reschedule.',
        ])->assertRedirect(route('visit-plans.index'));

        $this->assertDatabaseHas('visit_plans', ['id' => $visitPlan->id, 'status' => 'cancelled']);
    }

    public function test_updating_a_visit_plans_dealer_re_derives_its_territory_when_none_is_chosen(): void
    {
        $manager = $this->generalManager();
        $visitPlan = VisitPlan::factory()->create();
        $territory = Territory::factory()->create();
        $newDealer = Dealer::factory()->create(['territory_id' => $territory->id]);

        $this->actingAs($manager)->put(route('visit-plans.update', $visitPlan), [
            'user_id' => $visitPlan->user_id,
            'dealer_id' => $newDealer->id,
            'planned_date' => $visitPlan->planned_date->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ])->assertRedirect(route('visit-plans.index'));

        $this->assertDatabaseHas('visit_plans', [
            'id' => $visitPlan->id,
            'dealer_id' => $newDealer->id,
            'territory_id' => $territory->id,
        ]);
    }

    public function test_general_manager_cannot_delete_a_visit_plan(): void
    {
        $manager = $this->generalManager();
        $visitPlan = VisitPlan::factory()->create();

        $this->actingAs($manager)->delete(route('visit-plans.destroy', $visitPlan))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_visit_plan(): void
    {
        $admin = $this->superAdmin();
        $visitPlan = VisitPlan::factory()->create();

        $this->actingAs($admin)->delete(route('visit-plans.destroy', $visitPlan))
            ->assertRedirect(route('visit-plans.index'));
        $this->assertSoftDeleted('visit_plans', ['id' => $visitPlan->id]);

        $this->actingAs($admin)->post(route('visit-plans.restore', $visitPlan->id))
            ->assertRedirect(route('visit-plans.index'));
        $this->assertDatabaseHas('visit_plans', ['id' => $visitPlan->id, 'deleted_at' => null]);
    }

    public function test_territory_manager_can_view_and_edit_visit_plans_for_their_team(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $visitPlan = VisitPlan::factory()->create();

        $this->actingAs($manager)->get(route('visit-plans.index'))->assertOk();

        $this->actingAs($manager)->put(route('visit-plans.update', $visitPlan), [
            'user_id' => $visitPlan->user_id,
            'dealer_id' => $visitPlan->dealer_id,
            'planned_date' => $visitPlan->planned_date->toDateString(),
            'status' => VisitPlanStatus::Planned->value,
        ])->assertRedirect(route('visit-plans.index'));
    }

    public function test_territory_manager_cannot_delete_a_visit_plan(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $visitPlan = VisitPlan::factory()->create();

        $this->actingAs($manager)->delete(route('visit-plans.destroy', $visitPlan))->assertForbidden();
    }

    public function test_a_missed_plan_is_flagged_when_past_its_planned_date_and_still_planned(): void
    {
        $missed = VisitPlan::factory()->create(['planned_date' => Carbon::yesterday()->toDateString(), 'status' => VisitPlanStatus::Planned]);
        $fulfilled = VisitPlan::factory()->completed()->create(['planned_date' => Carbon::yesterday()->toDateString()]);

        $this->assertTrue($missed->isMissed());
        $this->assertFalse($fulfilled->isMissed());
    }
}
