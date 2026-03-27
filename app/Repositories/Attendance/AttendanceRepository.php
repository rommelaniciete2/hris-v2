<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AttendanceRepository
{
    public function todayRecord(Employee $employee, CarbonImmutable $workDate): ?Attendance
    {
        return Attendance::query()
            ->whereBelongsTo($employee)
            ->whereDate('work_date', $workDate)
            ->first();
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function recentLogs(Employee $employee, int $limit = 10): Collection
    {
        return Attendance::query()
            ->whereBelongsTo($employee)
            ->latest('work_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function between(Employee $employee, CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        return Attendance::query()
            ->whereBelongsTo($employee)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();
    }
}
