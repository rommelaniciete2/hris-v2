<?php

namespace App\Support\Hris;

use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;

class AttendanceCalculator
{
    /**
     * @return array{late_minutes:int, undertime_minutes:int, overtime_minutes:int, status:string}
     */
    public function summarize(
        CarbonImmutable $workDate,
        CarbonImmutable $clockInAt,
        ?CarbonImmutable $clockOutAt,
        WorkSchedule $workSchedule,
    ): array {
        $shiftStart = CarbonImmutable::parse($workDate->format('Y-m-d').' '.$workSchedule->starts_at);
        $shiftEnd = CarbonImmutable::parse($workDate->format('Y-m-d').' '.$workSchedule->ends_at);

        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd = $shiftEnd->addDay();
        }

        $lateGrace = $shiftStart->addMinutes($workSchedule->late_grace_minutes);

        $lateMinutes = $clockInAt->greaterThan($lateGrace)
            ? $this->wholeMinutesBetween($shiftStart, $clockInAt)
            : 0;

        $undertimeMinutes = 0;
        $overtimeMinutes = 0;
        $status = $lateMinutes > 0 ? 'late' : 'present';

        if ($clockOutAt === null) {
            return [
                'late_minutes' => $lateMinutes,
                'undertime_minutes' => 0,
                'overtime_minutes' => 0,
                'status' => 'in_progress',
            ];
        }

        if ($clockOutAt->lessThan($shiftEnd)) {
            $undertimeMinutes = $this->wholeMinutesBetween($clockOutAt, $shiftEnd);
        }

        if ($clockOutAt->greaterThan($shiftEnd)) {
            $overtimeMinutes = $this->wholeMinutesBetween($shiftEnd, $clockOutAt);
        }

        if ($undertimeMinutes > 0) {
            $status = 'undertime';
        }

        if ($overtimeMinutes > 0) {
            $status = $lateMinutes > 0 ? 'late_with_overtime' : 'overtime';
        }

        return [
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $status,
        ];
    }

    public function scheduledWorkMinutes(WorkSchedule $workSchedule): int
    {
        $start = CarbonImmutable::parse('2026-01-01 '.$workSchedule->starts_at);
        $end = CarbonImmutable::parse('2026-01-01 '.$workSchedule->ends_at);

        if ($end->lessThanOrEqualTo($start)) {
            $end = $end->addDay();
        }

        return max(1, $this->wholeMinutesBetween($start, $end) - $workSchedule->break_minutes);
    }

    private function wholeMinutesBetween(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (int) $start->diffInMinutes($end);
    }
}
