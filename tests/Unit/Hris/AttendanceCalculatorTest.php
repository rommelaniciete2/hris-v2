<?php

namespace Tests\Unit\Hris;

use App\Models\WorkSchedule;
use App\Support\Hris\AttendanceCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    public function test_it_truncates_fractional_late_minutes_to_a_whole_number(): void
    {
        $calculator = new AttendanceCalculator;

        $summary = $calculator->summarize(
            CarbonImmutable::parse('2026-03-27'),
            CarbonImmutable::parse('2026-03-27 10:08:28'),
            null,
            $this->workSchedule(),
        );

        $this->assertSame(128, $summary['late_minutes']);
        $this->assertSame(0, $summary['undertime_minutes']);
        $this->assertSame(0, $summary['overtime_minutes']);
        $this->assertSame('in_progress', $summary['status']);
    }

    public function test_it_truncates_fractional_undertime_minutes_to_a_whole_number(): void
    {
        $calculator = new AttendanceCalculator;

        $summary = $calculator->summarize(
            CarbonImmutable::parse('2026-03-27'),
            CarbonImmutable::parse('2026-03-27 08:00:00'),
            CarbonImmutable::parse('2026-03-27 16:44:45'),
            $this->workSchedule(),
        );

        $this->assertSame(0, $summary['late_minutes']);
        $this->assertSame(15, $summary['undertime_minutes']);
        $this->assertSame(0, $summary['overtime_minutes']);
        $this->assertSame('undertime', $summary['status']);
    }

    public function test_it_truncates_fractional_overtime_minutes_to_a_whole_number(): void
    {
        $calculator = new AttendanceCalculator;

        $summary = $calculator->summarize(
            CarbonImmutable::parse('2026-03-27'),
            CarbonImmutable::parse('2026-03-27 08:00:00'),
            CarbonImmutable::parse('2026-03-27 17:14:33'),
            $this->workSchedule(),
        );

        $this->assertSame(0, $summary['late_minutes']);
        $this->assertSame(0, $summary['undertime_minutes']);
        $this->assertSame(14, $summary['overtime_minutes']);
        $this->assertSame('overtime', $summary['status']);
    }

    private function workSchedule(): WorkSchedule
    {
        return new WorkSchedule([
            'name' => 'Day Shift',
            'starts_at' => '08:00:00',
            'ends_at' => '17:00:00',
            'late_grace_minutes' => 10,
            'break_minutes' => 60,
            'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'is_flexible' => false,
        ]);
    }
}
