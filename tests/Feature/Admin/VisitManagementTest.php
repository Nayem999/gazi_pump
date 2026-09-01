<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\VisitPlanStatus;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VisitManagementTest extends TestCase
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
        $this->get(route('visits.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_visits(): void
    {
        $this->actingAs($this->executive())->get(route('visits.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_backfill_a_visit(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $this->actingAs($manager)->get(route('visits.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('visits.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'check_in_at' => Carbon::yesterday()->setTime(10, 0)->format('Y-m-d H:i:s'),
            'check_out_at' => Carbon::yesterday()->setTime(10, 30)->format('Y-m-d H:i:s'),
            'feedback' => 'Backfilled from paper log.',
        ]);

        $response->assertRedirect(route('visits.index'));
        $this->assertDatabaseHas('visits', ['user_id' => $executive->id, 'dealer_id' => $dealer->id]);
    }

    public function test_backfilling_a_visit_completes_the_matching_visit_plan_for_that_date(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();
        $plan = VisitPlan::factory()->create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'planned_date' => Carbon::yesterday()->toDateString(),
            'status' => VisitPlanStatus::Planned,
        ]);

        $this->actingAs($manager)->post(route('visits.store'), [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'check_in_at' => Carbon::yesterday()->setTime(10, 0)->format('Y-m-d H:i:s'),
            'check_out_at' => Carbon::yesterday()->setTime(10, 30)->format('Y-m-d H:i:s'),
            'feedback' => 'Backfilled from paper log.',
        ])->assertRedirect(route('visits.index'));

        $this->assertSame(VisitPlanStatus::Completed, $plan->fresh()->status);
    }

    public function test_general_manager_can_view_a_visit_detail_page(): void
    {
        $manager = $this->generalManager();
        $visit = Visit::factory()->create();

        $this->actingAs($manager)->get(route('visits.show', $visit))->assertOk();
    }

    public function test_general_manager_can_update_a_visit(): void
    {
        $manager = $this->generalManager();
        $visit = Visit::factory()->create();

        $this->actingAs($manager)->put(route('visits.update', $visit), [
            'user_id' => $visit->user_id,
            'dealer_id' => $visit->dealer_id,
            'check_in_at' => $visit->check_in_at->format('Y-m-d H:i:s'),
            'feedback' => 'Corrected feedback note.',
        ])->assertRedirect(route('visits.index'));

        $this->assertDatabaseHas('visits', ['id' => $visit->id, 'feedback' => 'Corrected feedback note.']);
    }

    public function test_general_manager_cannot_delete_a_visit(): void
    {
        $manager = $this->generalManager();
        $visit = Visit::factory()->create();

        $this->actingAs($manager)->delete(route('visits.destroy', $visit))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_a_visit(): void
    {
        $admin = $this->superAdmin();
        $visit = Visit::factory()->create();

        $this->actingAs($admin)->delete(route('visits.destroy', $visit))
            ->assertRedirect(route('visits.index'));
        $this->assertSoftDeleted('visits', ['id' => $visit->id]);

        $this->actingAs($admin)->post(route('visits.restore', $visit->id))
            ->assertRedirect(route('visits.index'));
        $this->assertDatabaseHas('visits', ['id' => $visit->id, 'deleted_at' => null]);
    }

    public function test_territory_manager_can_view_but_not_edit_visits(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $visit = Visit::factory()->create();

        $this->actingAs($manager)->get(route('visits.index'))->assertOk();

        $this->actingAs($manager)->put(route('visits.update', $visit), [
            'user_id' => $visit->user_id,
            'dealer_id' => $visit->dealer_id,
            'check_in_at' => $visit->check_in_at->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }
}
