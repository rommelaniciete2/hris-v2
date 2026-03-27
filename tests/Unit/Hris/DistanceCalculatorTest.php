<?php

namespace Tests\Unit\Hris;

use App\Support\Hris\DistanceCalculator;
use PHPUnit\Framework\TestCase;

class DistanceCalculatorTest extends TestCase
{
    public function test_it_returns_zero_for_the_same_coordinates(): void
    {
        $calculator = new DistanceCalculator;

        $distance = $calculator->inMeters(14.5547291, 121.0244452, 14.5547291, 121.0244452);

        $this->assertEquals(0.0, $distance);
    }

    public function test_it_calculates_distance_in_meters(): void
    {
        $calculator = new DistanceCalculator;

        $distance = $calculator->inMeters(14.5547291, 121.0244452, 14.5550000, 121.0250000);

        $this->assertGreaterThan(60, $distance);
        $this->assertLessThan(80, $distance);
    }
}
