<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The entitlement setup screen - how many days people are granted.
 *
 * Kept separate from leave-requests throughout: approving a request spends
 * days someone already has, whereas this decides how many they get, and
 * the tests below pin that a manager who can do the first cannot do the
 * second.
 */
class LeaveEntitlementSetupTest extends TestCase
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

    public function test_a_general_manager_can_open_the_setup_screen(): void
    {
        $this->actingAs($this->generalManager())
            ->get(route('leave-balances.index'))
            ->assertOk()
            ->assertSee('Set Up Entitlements');
    }

    public function test_setting_up_seeds_an_entitlement_per_person_per_active_type(): void
    {
        $manager = $this->generalManager();
        $this->executive();
        $this->executive();

        LeaveType::factory()->create(['annual_quota' => 10]);
        LeaveType::factory()->create(['annual_quota' => 5]);
        LeaveType::factory()->inactive()->create(['annual_quota' => 99]);

        $this->actingAs($manager)
            ->post(route('leave-balances.set-up'), ['year' => 2026])
            ->assertRedirect()
            ->assertSessionHas('success');

        // 3 users (2 executives + the manager) x 2 active types.
        $this->assertSame(6, LeaveBalance::where('year', 2026)->count());
        $this->assertSame(0, LeaveBalance::query()
            ->whereHas('leaveType', fn ($q) => $q->where('status', false))
            ->count(), 'an inactive type is not granted');
    }

    public function test_the_quota_becomes_the_entitlement(): void
    {
        $manager = $this->generalManager();
        LeaveType::factory()->create(['annual_quota' => 14]);

        $this->actingAs($manager)->post(route('leave-balances.set-up'), ['year' => 2026]);

        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $manager->id,
            'year' => 2026,
            'entitled_days' => '14.0',
        ]);
    }

    public function test_re_running_the_setup_never_overwrites_an_adjusted_entitlement(): void
    {
        // The whole reason the action is safe to press twice.
        $manager = $this->generalManager();
        LeaveType::factory()->create(['annual_quota' => 10]);

        $this->actingAs($manager)->post(route('leave-balances.set-up'), ['year' => 2026]);
        LeaveBalance::query()->update(['entitled_days' => 25]);

        $this->actingAs($manager)
            ->post(route('leave-balances.set-up'), ['year' => 2026])
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'Nothing to set up'));

        $this->assertDatabaseHas('leave_balances', ['entitled_days' => '25.0']);
    }

    public function test_a_deliberately_deleted_entitlement_is_not_silently_reinstated(): void
    {
        // Deleting an entitlement is a decision; a bulk set-up must not
        // quietly undo it.
        $manager = $this->generalManager();
        LeaveType::factory()->create(['annual_quota' => 10]);

        $this->actingAs($manager)->post(route('leave-balances.set-up'), ['year' => 2026]);
        LeaveBalance::query()->delete();

        $this->actingAs($manager)->post(route('leave-balances.set-up'), ['year' => 2026]);

        $this->assertSame(0, LeaveBalance::count(), 'still deleted');
        $this->assertSame(1, LeaveBalance::withTrashed()->count(), 'and not duplicated either');
    }

    public function test_setting_up_can_be_limited_to_named_employees(): void
    {
        $manager = $this->generalManager();
        $joiner = $this->executive();
        $this->executive();
        LeaveType::factory()->create(['annual_quota' => 10]);

        $this->actingAs($manager)->post(route('leave-balances.set-up'), [
            'year' => 2026,
            'user_ids' => [$joiner->id],
        ])->assertRedirect();

        $this->assertSame(1, LeaveBalance::count());
        $this->assertDatabaseHas('leave_balances', ['user_id' => $joiner->id]);
    }

    public function test_an_entitlement_can_be_created_by_hand(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        $this->actingAs($manager)->post(route('leave-balances.store'), [
            'user_id' => $executive->id,
            'leave_type_id' => $type->id,
            'year' => 2026,
            'entitled_days' => 12.5,
            'carried_forward_days' => 2.5,
            'remarks' => 'Pro-rated, joined in March.',
        ])->assertRedirect();

        $this->assertDatabaseHas('leave_balances', [
            'user_id' => $executive->id,
            'entitled_days' => '12.5',
            'carried_forward_days' => '2.5',
        ]);
    }

    public function test_saving_over_a_soft_deleted_entitlement_revives_it_rather_than_failing(): void
    {
        // leave_balances is unique on (user_id, leave_type_id, year) and
        // soft-deletes, so a trashed row still holds the key. A plain
        // insert would hit a duplicate-key error that reads as a bug.
        $manager = $this->generalManager();
        $executive = $this->executive();
        $type = LeaveType::factory()->create();

        $balance = LeaveBalance::factory()->create([
            'user_id' => $executive->id,
            'leave_type_id' => $type->id,
            'year' => 2026,
            'entitled_days' => 10,
        ]);
        $balance->delete();

        $this->actingAs($manager)->post(route('leave-balances.store'), [
            'user_id' => $executive->id,
            'leave_type_id' => $type->id,
            'year' => 2026,
            'entitled_days' => 15,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(1, LeaveBalance::withTrashed()->count(), 'revived, not duplicated');
        $this->assertSame($balance->id, LeaveBalance::first()->id, 'the same record, so its history survives');
        $this->assertSame('15.0', LeaveBalance::first()->entitled_days);
    }

    public function test_editing_changes_the_numbers_but_not_who_it_is_for(): void
    {
        $manager = $this->generalManager();
        $balance = LeaveBalance::factory()->create(['entitled_days' => 10]);
        $originalUser = $balance->user_id;

        $this->actingAs($manager)->put(route('leave-balances.update', $balance), [
            'entitled_days' => 18,
            'carried_forward_days' => 1,
            // Ignored: moving an entitlement would re-grant one person's
            // days to another.
            'user_id' => User::factory()->create()->id,
        ])->assertRedirect();

        $balance->refresh();
        $this->assertSame('18.0', $balance->entitled_days);
        $this->assertSame($originalUser, $balance->user_id);
    }

    public function test_deleting_an_entitlement_falls_back_to_the_type_default(): void
    {
        $manager = $this->generalManager();
        $balance = LeaveBalance::factory()->create();

        $this->actingAs($manager)
            ->delete(route('leave-balances.destroy', $balance))
            ->assertRedirect()
            ->assertSessionHas('success', fn (string $msg) => str_contains($msg, 'leave type default'));

        $this->assertSoftDeleted('leave_balances', ['id' => $balance->id]);
    }

    public function test_an_entitlement_beyond_a_years_worth_is_rejected(): void
    {
        $this->actingAs($this->generalManager())->post(route('leave-balances.store'), [
            'user_id' => $this->executive()->id,
            'leave_type_id' => LeaveType::factory()->create()->id,
            'year' => 2026,
            'entitled_days' => 400,
        ])->assertSessionHasErrors('entitled_days');
    }

    public function test_a_line_manager_who_can_approve_leave_still_cannot_grant_days(): void
    {
        // The separation this module exists to keep: approving spends
        // days, granting decides how many there are.
        $manager = $this->territoryManager();

        $this->assertTrue($manager->can('leave-requests.approve'));

        $this->actingAs($manager)->get(route('leave-balances.index'))->assertForbidden();
        $this->actingAs($manager)->post(route('leave-balances.set-up'), ['year' => 2026])->assertForbidden();
    }

    public function test_an_executive_cannot_reach_entitlements_at_all(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('leave-balances.index'))->assertForbidden();
        $this->actingAs($executive)->post(route('leave-balances.set-up'), ['year' => 2026])->assertForbidden();
        $this->actingAs($executive)->post(route('leave-balances.store'), [
            'user_id' => $executive->id,
            'leave_type_id' => LeaveType::factory()->create()->id,
            'year' => 2026,
            'entitled_days' => 99,
        ])->assertForbidden();
    }

    public function test_the_balance_screen_reflects_an_entitlement_set_up_here(): void
    {
        // End to end: what this screen grants is what the read-only
        // balance view reports.
        $manager = $this->generalManager();
        $executive = $this->executive();
        LeaveType::factory()->create(['name' => 'Annual Leave', 'annual_quota' => 20]);

        $this->actingAs($manager)->post(route('leave-balances.set-up'), [
            'year' => (int) now()->format('Y'),
            'user_ids' => [$executive->id],
        ]);

        LeaveBalance::query()->update(['entitled_days' => 22, 'carried_forward_days' => 3]);

        $this->actingAs($manager)
            ->get(route('leave-requests.balances', ['user_id' => $executive->id]))
            ->assertOk()
            ->assertSee('Annual Leave')
            ->assertSee('22')
            // 22 + 3 carried forward.
            ->assertSee('25');
    }
}
