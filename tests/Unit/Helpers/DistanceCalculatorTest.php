<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\DistanceCalculator;
use Tests\TestCase;

class DistanceCalculatorTest extends TestCase
{
    public function test_distance_between_identical_points_is_zero(): void
    {
        $this->assertSame(0.0, DistanceCalculator::haversineKm(23.8103, 90.4125, 23.8103, 90.4125));
    }

    public function test_one_degree_of_latitude_is_approximately_111_km(): void
    {
        $distance = DistanceCalculator::haversineKm(0.0, 0.0, 1.0, 0.0);

        $this->assertEqualsWithDelta(111.19, $distance, 0.5);
    }

    public function test_total_distance_sums_consecutive_legs_not_a_direct_line(): void
    {
        $pointA = ['lat' => 23.8103, 'lng' => 90.4125];
        $pointB = ['lat' => 23.8200, 'lng' => 90.4200];
        $pointC = ['lat' => 23.8103, 'lng' => 90.4125]; // back to the start

        $legAtoB = DistanceCalculator::haversineKm($pointA['lat'], $pointA['lng'], $pointB['lat'], $pointB['lng']);
        $legBtoC = DistanceCalculator::haversineKm($pointB['lat'], $pointB['lng'], $pointC['lat'], $pointC['lng']);

        $total = DistanceCalculator::totalDistanceKm([$pointA, $pointB, $pointC]);

        // A round trip back to the start covers real distance (2x one leg),
        // not the 0 km a straight-line "displacement" measure would give.
        $this->assertEqualsWithDelta($legAtoB + $legBtoC, $total, 0.0001);
        $this->assertGreaterThan(0.0, $total);
    }

    public function test_total_distance_of_a_single_point_is_zero(): void
    {
        $this->assertSame(0.0, DistanceCalculator::totalDistanceKm([['lat' => 23.8103, 'lng' => 90.4125]]));
    }

    public function test_total_distance_of_no_points_is_zero(): void
    {
        $this->assertSame(0.0, DistanceCalculator::totalDistanceKm([]));
    }
}
