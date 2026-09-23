<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ApprovalStatus;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use App\Models\Visit;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The order figures on the Visit Compliance report: what the visits
 * turned into.
 *
 * Most of what is pinned here is about what does NOT count - rejected
 * orders, trashed orders, a dealer who ordered through somebody else -
 * because each of those is a way the report would quietly flatter a rep.
 */
class VisitComplianceOrderMetricsTest extends TestCase
{
    use RefreshDatabase;

    private const FROM = '2026-09-01';

    private const TO = '2026-09-30';

    private function report(): \Illuminate\Support\Collection
    {
        return app(ReportService::class)->visitCompliance(['date_from' => self::FROM, 'date_to' => self::TO]);
    }

    private function rowFor(User $user): object
    {
        $row = $this->report()->first(fn ($r) => $r->user?->id === $user->id);

        $this->assertNotNull($row, "no report row for {$user->name}");

        return $row;
    }

    /** An order on a fixed in-range date, so nothing depends on "today". */
    private function order(User $rep, Dealer $dealer, float $amount, ApprovalStatus $status = ApprovalStatus::Approved): Order
    {
        return Order::factory()->create([
            'user_id' => $rep->id,
            'dealer_id' => $dealer->id,
            'order_date' => '2026-09-15',
            'total_amount' => $amount,
            'status' => $status->value,
        ]);
    }

    /** A visit pinned to midday in range - the factory's "now minus hours" can cross midnight. */
    private function visit(User $rep, Dealer $dealer): Visit
    {
        return Visit::factory()->create([
            'user_id' => $rep->id,
            'dealer_id' => $dealer->id,
            'check_in_at' => Carbon::parse('2026-09-15 12:00:00'),
            'check_out_at' => Carbon::parse('2026-09-15 12:30:00'),
        ]);
    }

    public function test_it_totals_order_value_and_count(): void
    {
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->order($rep, $dealer, 1000);
        $this->order($rep, $dealer, 3000);

        $row = $this->rowFor($rep);

        $this->assertSame(2, $row->order_count);
        $this->assertSame(4000.0, $row->order_value);
    }

    public function test_the_average_is_value_divided_by_orders(): void
    {
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->order($rep, $dealer, 1000);
        $this->order($rep, $dealer, 2000);
        $this->order($rep, $dealer, 6000);

        $this->assertSame(3000.0, $this->rowFor($rep)->avg_order_value);
    }

    public function test_rejected_orders_count_for_nothing(): void
    {
        // A manager threw it out; crediting the rep with it would report
        // sales that did not happen.
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->order($rep, $dealer, 1000);
        $this->order($rep, $dealer, 50000, ApprovalStatus::Rejected);

        $row = $this->rowFor($rep);

        $this->assertSame(1, $row->order_count);
        $this->assertSame(1000.0, $row->order_value);
    }

    public function test_pending_orders_do_count(): void
    {
        // Real field work awaiting a decision. Leaving it out would make
        // the current week read as idle.
        $rep = User::factory()->create();

        $this->order($rep, Dealer::factory()->create(), 2500, ApprovalStatus::Pending);

        $this->assertSame(2500.0, $this->rowFor($rep)->order_value);
    }

    public function test_a_rejected_orders_dealer_is_not_productive(): void
    {
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->visit($rep, $dealer);
        $this->order($rep, $dealer, 9000, ApprovalStatus::Rejected);

        $row = $this->rowFor($rep);

        $this->assertSame(0, $row->productive_dealers);
        $this->assertSame(0.0, $row->strike_rate);
    }

    public function test_productive_dealers_counts_each_dealer_once(): void
    {
        // "How many dealers did orders come from" - three orders from the
        // same dealer is still one dealer.
        $rep = User::factory()->create();
        $dealerA = Dealer::factory()->create();
        $dealerB = Dealer::factory()->create();

        $this->order($rep, $dealerA, 100);
        $this->order($rep, $dealerA, 100);
        $this->order($rep, $dealerA, 100);
        $this->order($rep, $dealerB, 100);

        $this->assertSame(2, $this->rowFor($rep)->productive_dealers);
    }

    public function test_productive_dealers_includes_orders_taken_without_a_visit(): void
    {
        // A phone order is still a dealer an order came from.
        $rep = User::factory()->create();

        $this->order($rep, Dealer::factory()->create(), 500);

        $this->assertSame(1, $this->rowFor($rep)->productive_dealers);
    }

    public function test_strike_rate_is_the_share_of_visited_dealers_who_ordered(): void
    {
        $rep = User::factory()->create();
        [$a, $b, $c, $d] = Dealer::factory()->count(4)->create()->all();

        foreach ([$a, $b, $c, $d] as $dealer) {
            $this->visit($rep, $dealer);
        }

        $this->order($rep, $a, 100);
        $this->order($rep, $b, 100);
        $this->order($rep, $c, 100);

        $row = $this->rowFor($rep);

        $this->assertSame(4, $row->visited_dealers);
        $this->assertSame(3, $row->converted_dealers);
        $this->assertSame(75.0, $row->strike_rate);
    }

    public function test_strike_rate_cannot_exceed_one_hundred_percent(): void
    {
        // Orders from dealers who were never visited raise Productive
        // Dealers but must not push the visit strike rate past 100%.
        $rep = User::factory()->create();
        $visited = Dealer::factory()->create();

        $this->visit($rep, $visited);
        $this->order($rep, $visited, 100);
        $this->order($rep, Dealer::factory()->create(), 100);
        $this->order($rep, Dealer::factory()->create(), 100);

        $row = $this->rowFor($rep);

        $this->assertSame(3, $row->productive_dealers);
        $this->assertSame(100.0, $row->strike_rate);
    }

    public function test_a_dealer_ordering_through_another_rep_is_not_this_reps_conversion(): void
    {
        // Rep A visited; the dealer then ordered through rep B. That is
        // not A's visit paying off.
        $repA = User::factory()->create();
        $repB = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->visit($repA, $dealer);
        $this->order($repB, $dealer, 5000);

        $row = $this->rowFor($repA);

        $this->assertSame(1, $row->visited_dealers);
        $this->assertSame(0, $row->converted_dealers);
        $this->assertSame(0.0, $row->strike_rate);
    }

    public function test_a_trashed_order_is_not_a_conversion(): void
    {
        // The strike-rate subquery is raw SQL with no soft-delete scope of
        // its own, so this is the case most likely to regress.
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->visit($rep, $dealer);
        $this->order($rep, $dealer, 5000)->delete();

        $row = $this->rowFor($rep);

        $this->assertSame(0, $row->converted_dealers);
        $this->assertSame(0, $row->order_count);
    }

    public function test_orders_outside_the_period_are_ignored(): void
    {
        $rep = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->order($rep, $dealer, 1000);
        Order::factory()->create([
            'user_id' => $rep->id,
            'dealer_id' => $dealer->id,
            'order_date' => '2026-10-05',
            'total_amount' => 99999,
            'status' => ApprovalStatus::Approved->value,
        ]);

        $this->assertSame(1000.0, $this->rowFor($rep)->order_value);
    }

    public function test_a_rep_who_only_took_orders_still_appears(): void
    {
        // No plan, no visit - but the order value is exactly what this
        // report now shows, so hiding them would hide it.
        $rep = User::factory()->create();

        $this->order($rep, Dealer::factory()->create(), 750);

        $row = $this->rowFor($rep);

        $this->assertSame(0, $row->total_visits);
        $this->assertSame(750.0, $row->order_value);
    }

    public function test_no_orders_means_zeros_not_errors(): void
    {
        $rep = User::factory()->create();

        $this->visit($rep, Dealer::factory()->create());

        $row = $this->rowFor($rep);

        $this->assertSame(0, $row->order_count);
        $this->assertSame(0.0, $row->avg_order_value, 'no division by zero');
        $this->assertSame(0.0, $row->strike_rate);
    }
}
