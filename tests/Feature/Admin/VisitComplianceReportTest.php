<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ApprovalStatus;
use App\Exports\VisitComplianceExport;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The Visit Compliance page with its order columns: what renders, what
 * the footer totals, and what the export carries. The arithmetic itself
 * is pinned in Tests\Unit\Services\VisitComplianceOrderMetricsTest.
 */
class VisitComplianceReportTest extends TestCase
{
    use RefreshDatabase;

    private const RANGE = ['date_from' => '2026-09-01', 'date_to' => '2026-09-30'];

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

    private function order(User $rep, Dealer $dealer, float $amount, ApprovalStatus $status = ApprovalStatus::Approved): void
    {
        Order::factory()->create([
            'user_id' => $rep->id,
            'dealer_id' => $dealer->id,
            'order_date' => '2026-09-15',
            'total_amount' => $amount,
            'status' => $status->value,
        ]);
    }

    public function test_the_page_shows_the_order_columns_with_real_figures(): void
    {
        $rep = User::factory()->create(['name' => 'Karim Uddin']);
        $visited = Dealer::factory()->create();
        $notVisited = Dealer::factory()->create();

        Visit::factory()->create([
            'user_id' => $rep->id,
            'dealer_id' => $visited->id,
            'check_in_at' => Carbon::parse('2026-09-15 12:00:00'),
            'check_out_at' => Carbon::parse('2026-09-15 12:30:00'),
        ]);
        $this->order($rep, $visited, 10000);
        $this->order($rep, $notVisited, 5000);

        $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance', self::RANGE))
            ->assertOk()
            ->assertSeeInOrder(['Orders', 'Order Value', 'Avg Order Value', 'Productive Dealers', 'Strike Rate'])
            ->assertSee('Karim Uddin')
            ->assertSee('15,000.00')
            ->assertSee('7,500.00')
            ->assertSee('1 of 1 visited');
    }

    public function test_the_footer_totals_every_executive_not_just_the_current_page(): void
    {
        // 21 executives with one order each: the page shows 20, the footer
        // must still count all 21.
        $dealer = Dealer::factory()->create();

        foreach (range(1, 21) as $i) {
            $this->order(User::factory()->create(), $dealer, 1000);
        }

        $response = $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance', self::RANGE));

        $response->assertOk();
        $this->assertSame(21, $response->viewData('totals')['order_count']);
        $this->assertSame(21000.0, $response->viewData('totals')['order_value']);
        $response->assertSee('21,000.00');
    }

    public function test_the_footer_average_is_recomputed_from_the_totals(): void
    {
        // Rep A: one order of 1,000. Rep B: three of 3,000. The average of
        // averages would be 2,000; the true average is 10,000 / 4 = 2,500.
        $dealer = Dealer::factory()->create();
        $repA = User::factory()->create();
        $repB = User::factory()->create();

        $this->order($repA, $dealer, 1000);
        $this->order($repB, $dealer, 3000);
        $this->order($repB, $dealer, 3000);
        $this->order($repB, $dealer, 3000);

        $response = $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance', self::RANGE));

        $this->assertSame(2500.0, $response->viewData('totals')['avg_order_value']);
    }

    public function test_rejected_orders_are_left_out_of_the_footer(): void
    {
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->order($rep, $dealer, 1000);
        $this->order($rep, $dealer, 90000, ApprovalStatus::Rejected);

        $response = $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance', self::RANGE));

        $this->assertSame(1000.0, $response->viewData('totals')['order_value']);
        $response->assertDontSee('91,000.00');
    }

    public function test_the_export_carries_the_order_columns_as_numbers(): void
    {
        $rep = User::factory()->create();
        $this->order($rep, Dealer::factory()->create(), 1234.5);

        $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance.export', self::RANGE))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $export = new VisitComplianceExport(
            app(\App\Services\ReportService::class)->visitCompliance(self::RANGE),
        );

        foreach (['Orders', 'Order Value', 'Avg Order Value', 'Productive Dealers', 'Visited Dealers', 'Visited Dealers Who Ordered', 'Strike Rate %'] as $heading) {
            $this->assertContains($heading, $export->headings());
        }

        $row = $export->map($export->collection()->first(fn ($r) => $r->user?->id === $rep->id));
        $valueColumn = array_search('Order Value', $export->headings(), true);

        // A number, not "1,234.50", so the spreadsheet can still sum it.
        $this->assertSame(1234.5, $row[$valueColumn]);
    }

    public function test_the_pdf_still_prints(): void
    {
        $this->order(User::factory()->create(), Dealer::factory()->create(), 500);

        $this->actingAs($this->generalManager())
            ->get(route('reports.visit-compliance.print', self::RANGE))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
