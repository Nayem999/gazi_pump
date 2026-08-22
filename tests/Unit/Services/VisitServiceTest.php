<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Dealer;
use App\Services\VisitService;
use Tests\TestCase;

class VisitServiceTest extends TestCase
{
    private function service(): VisitService
    {
        return app(VisitService::class);
    }

    public function test_check_in_at_the_exact_dealer_location_is_verified_with_zero_distance(): void
    {
        $dealer = new Dealer(['gps_lat' => 23.8103, 'gps_lng' => 90.4125]);

        [$verified, $distance] = $this->service()->verifyGpsProximity($dealer, 23.8103, 90.4125);

        $this->assertTrue($verified);
        $this->assertSame(0.0, $distance);
    }

    public function test_check_in_within_the_configured_radius_is_verified(): void
    {
        config(['sfa.visits.gps_verification_radius_meters' => 300]);
        $dealer = new Dealer(['gps_lat' => 23.8103, 'gps_lng' => 90.4125]);

        // Roughly 100m north of the dealer's pin.
        [$verified, $distance] = $this->service()->verifyGpsProximity($dealer, 23.8112, 90.4125);

        $this->assertTrue($verified);
        $this->assertLessThan(300, $distance);
    }

    public function test_check_in_beyond_the_configured_radius_is_unverified(): void
    {
        config(['sfa.visits.gps_verification_radius_meters' => 300]);
        $dealer = new Dealer(['gps_lat' => 23.8103, 'gps_lng' => 90.4125]);

        [$verified, $distance] = $this->service()->verifyGpsProximity($dealer, 23.9000, 90.5000);

        $this->assertFalse($verified);
        $this->assertGreaterThan(300, $distance);
    }

    public function test_a_dealer_with_no_gps_pin_yields_unknown_not_unverified(): void
    {
        $dealer = new Dealer(['gps_lat' => null, 'gps_lng' => null]);

        [$verified, $distance] = $this->service()->verifyGpsProximity($dealer, 23.8103, 90.4125);

        $this->assertNull($verified);
        $this->assertNull($distance);
    }
}
