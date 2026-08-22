<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
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
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access_to_any_report(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('reports.attendance-summary'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.visit-compliance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.order-performance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.collection-summary'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.territory-performance'))->assertForbidden();
    }

    public function test_general_manager_can_view_every_report(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->actingAs($manager)->get(route('reports.attendance-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.visit-compliance'))->assertOk();
        $this->actingAs($manager)->get(route('reports.order-performance'))->assertOk();
        $this->actingAs($manager)->get(route('reports.collection-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.territory-performance'))->assertOk();
    }

    public function test_territory_manager_can_also_view_reports(): void
    {
        $manager = $this->territoryManager();

        $this->actingAs($manager)->get(route('reports.order-performance'))->assertOk();
    }

    public function test_order_performance_report_reflects_real_data(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        Order::factory()->create(['user_id' => $executive->id, 'order_date' => now()->toDateString(), 'total_amount' => 5000]);

        $response = $this->actingAs($manager)->get(route('reports.order-performance'));

        $response->assertOk()->assertSee($executive->name)->assertSee('5,000.00');
    }

    public function test_reports_can_be_exported_to_excel(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.order-performance.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_reports_can_be_printed_as_pdf(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.order-performance.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
