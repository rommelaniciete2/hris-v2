<?php

namespace App\Repositories\Reports;

use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PerformanceReview;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ReportRepository
{
    /**
     * @return array<string, int>
     */
    public function summaryCards(): array
    {
        return [
            'employees' => Employee::query()->count(),
            'present_today' => Attendance::query()->whereDate('work_date', CarbonImmutable::today())->count(),
            'pending_leaves' => LeaveRequest::query()->where('status', 'pending')->count(),
            'open_recruitment' => Applicant::query()->whereNotIn('stage', ['hired', 'rejected'])->count(),
        ];
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function recentAttendance(): Collection
    {
        return Attendance::query()
            ->with('employee.user')
            ->latest('work_date')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Payroll>
     */
    public function recentPayrolls(): Collection
    {
        return Payroll::query()
            ->with('employee.user')
            ->latest('period_end')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, PerformanceReview>
     */
    public function recentPerformanceReviews(): Collection
    {
        return PerformanceReview::query()
            ->with(['employee.user', 'reviewer'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Employee>
     */
    public function recentEmployees(): Collection
    {
        return Employee::query()
            ->with(['user.role', 'department', 'position'])
            ->latest()
            ->limit(10)
            ->get();
    }
}
