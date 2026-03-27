<?php

namespace App\Repositories\Leaves;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LeaveRepository
{
    /**
     * @return Collection<int, LeaveBalance>
     */
    public function balances(Employee $employee, int $year): Collection
    {
        return LeaveBalance::query()
            ->with('leaveType')
            ->whereBelongsTo($employee)
            ->where('year', $year)
            ->get();
    }

    /**
     * @return Collection<int, LeaveRequest>
     */
    public function historyFor(User $user): Collection
    {
        $query = LeaveRequest::query()->with(['employee.user', 'leaveType', 'approver']);

        if (! $user->hasAnyHrisPermission(['leaves.view_any', 'leaves.manage'])) {
            $query->where('employee_id', $user->employee?->id);
        }

        return $query->latest('start_date')->limit(20)->get();
    }

    /**
     * @return Collection<int, LeaveRequest>
     */
    public function pendingApprovals(): Collection
    {
        return LeaveRequest::query()
            ->with(['employee.user', 'leaveType'])
            ->where('status', 'pending')
            ->orderBy('start_date')
            ->get();
    }

    public function currentYear(): int
    {
        return CarbonImmutable::now()->year;
    }
}
