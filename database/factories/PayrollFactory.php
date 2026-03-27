<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payroll>
 */
class PayrollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->startOfMonth()->addDays(14)->toDateString(),
            'pay_date' => now()->toDateString(),
            'status' => 'processed',
            'basic_salary' => 15000,
            'late_deduction' => 0,
            'undertime_deduction' => 0,
            'absent_deduction' => 0,
            'overtime_pay' => 0,
            'allowances' => 0,
            'gross_pay' => 15000,
            'total_deductions' => 1000,
            'net_pay' => 14000,
            'earnings_breakdown' => ['basic_salary' => 15000],
            'deductions_breakdown' => ['sss' => 400, 'tax' => 600],
            'payslip_snapshot' => ['employee_name' => fake()->name()],
            'processed_by' => User::factory(),
        ];
    }
}
