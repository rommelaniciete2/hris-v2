<?php

namespace App\Services\Leaves;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function submit(User $user, array $validated): LeaveRequest
    {
        $user->loadMissing('employee.leaveBalances.leaveType');

        if ($user->employee === null) {
            throw ValidationException::withMessages([
                'employee' => 'Employee profile not found.',
            ]);
        }

        $startDate = CarbonImmutable::parse($validated['start_date']);
        $endDate = CarbonImmutable::parse($validated['end_date']);
        $days = $this->countWeekdays($startDate, $endDate);

        $balance = LeaveBalance::query()
            ->where('employee_id', $user->employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $startDate->year)
            ->first();

        if ($balance !== null && $balance->remainingDays() < $days) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Insufficient leave balance for this request.',
            ]);
        }

        return LeaveRequest::query()->create([
            'employee_id' => $user->employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ])->fresh(['employee.user', 'leaveType']);
    }

    public function approve(LeaveRequest $leaveRequest, User $approver, string $status, ?string $rejectionReason = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approver, $status, $rejectionReason) {
            $alreadyApproved = $leaveRequest->status === 'approved';

            $leaveRequest->forceFill([
                'status' => $status,
                'approver_id' => $approver->id,
                'approved_at' => $status === 'approved' ? now() : null,
                'rejection_reason' => $status === 'rejected' ? $rejectionReason : null,
            ])->save();

            if ($status === 'approved' && ! $alreadyApproved) {
                $balance = LeaveBalance::query()->firstOrCreate(
                    [
                        'employee_id' => $leaveRequest->employee_id,
                        'leave_type_id' => $leaveRequest->leave_type_id,
                        'year' => CarbonImmutable::parse($leaveRequest->start_date)->year,
                    ],
                    [
                        'allocated_days' => 0,
                        'used_days' => 0,
                    ],
                );

                $balance->increment('used_days', $leaveRequest->days);
            }

            return $leaveRequest->fresh(['employee.user', 'leaveType', 'approver']);
        });
    }

    private function countWeekdays(CarbonImmutable $startDate, CarbonImmutable $endDate): float
    {
        $days = 0;

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if ($date->isWeekday()) {
                $days++;
            }
        }

        return (float) $days;
    }
}
