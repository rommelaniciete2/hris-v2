<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use App\Repositories\Attendance\AttendanceRepository;
use App\Support\Hris\AttendanceCalculator;
use App\Support\Hris\StatutoryDeductionCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PayrollService
{
    public function __construct(
        public AttendanceRepository $attendanceRepository,
        public AttendanceCalculator $attendanceCalculator,
        public StatutoryDeductionCalculator $statutoryDeductionCalculator,
    ) {}

    /**
     * @return Collection<int, Payroll>
     */
    public function run(CarbonImmutable $periodStart, CarbonImmutable $periodEnd, CarbonImmutable $payDate, User $actor): Collection
    {
        $payrolls = collect();

        $employees = Employee::query()
            ->with(['user', 'workSchedule'])
            ->where('employment_status', 'active')
            ->get();

        foreach ($employees as $employee) {
            $payrolls->push($this->runForEmployee($employee, $periodStart, $periodEnd, $payDate, $actor));
        }

        return $payrolls;
    }

    public function runForEmployee(
        Employee $employee,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        CarbonImmutable $payDate,
        User $actor,
    ): Payroll {
        $attendances = $this->attendanceRepository->between($employee, $periodStart, $periodEnd);
        $monthlySalary = (float) $employee->base_salary;
        $periodBase = round($monthlySalary / 2, 2);

        $scheduledMinutes = $employee->workSchedule !== null
            ? $this->attendanceCalculator->scheduledWorkMinutes($employee->workSchedule)
            : 480;

        $minuteRate = $monthlySalary > 0
            ? round($monthlySalary / 22 / max(1, $scheduledMinutes), 6)
            : 0;

        $lateMinutes = (int) $attendances->sum('late_minutes');
        $undertimeMinutes = (int) $attendances->sum('undertime_minutes');
        $overtimeMinutes = (int) $attendances->sum('overtime_minutes');

        $lateDeduction = round($minuteRate * $lateMinutes, 2);
        $undertimeDeduction = round($minuteRate * $undertimeMinutes, 2);
        $absentDeduction = 0.0;
        $overtimePay = round($minuteRate * 1.25 * $overtimeMinutes, 2);

        $statutoryDeductions = $this->statutoryDeductionCalculator->calculateSemiMonthly($monthlySalary, $payDate);
        $statutoryTotal = array_sum($statutoryDeductions);
        $grossPay = round($periodBase + $overtimePay, 2);
        $totalDeductions = round($lateDeduction + $undertimeDeduction + $absentDeduction + $statutoryTotal, 2);
        $netPay = round($grossPay - $totalDeductions, 2);

        return Payroll::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'pay_date' => $payDate->toDateString(),
                'status' => 'processed',
                'basic_salary' => $periodBase,
                'late_deduction' => $lateDeduction,
                'undertime_deduction' => $undertimeDeduction,
                'absent_deduction' => $absentDeduction,
                'overtime_pay' => $overtimePay,
                'allowances' => 0,
                'gross_pay' => $grossPay,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'earnings_breakdown' => [
                    'basic_salary' => $periodBase,
                    'overtime_pay' => $overtimePay,
                ],
                'deductions_breakdown' => array_merge([
                    'late' => $lateDeduction,
                    'undertime' => $undertimeDeduction,
                    'absent' => $absentDeduction,
                ], $statutoryDeductions),
                'payslip_snapshot' => [
                    'employee_name' => $employee->fullName(),
                    'employee_number' => $employee->employee_number,
                    'period' => [
                        'start' => $periodStart->toDateString(),
                        'end' => $periodEnd->toDateString(),
                    ],
                ],
                'processed_by' => $actor->id,
            ],
        );
    }
}
