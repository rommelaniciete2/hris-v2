<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Role;
use App\Models\StatutoryDeductionTable;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = collect(config('hris.roles'))
            ->map(fn (array $permissions, string $slug) => Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => str($slug)->headline()->toString(),
                    'permissions' => $permissions,
                ],
            ))
            ->keyBy('slug');

        $hrDepartment = Department::query()->updateOrCreate(
            ['code' => 'HR'],
            ['name' => 'Human Resources', 'description' => 'HR operations and employee services.'],
        );

        $financeDepartment = Department::query()->updateOrCreate(
            ['code' => 'FIN'],
            ['name' => 'Finance', 'description' => 'Payroll and finance operations.'],
        );

        $developerDepartment = Department::query()->updateOrCreate(
            ['code' => 'OPS'],
            ['name' => 'Operations', 'description' => 'Operations and general administration.'],
        );

        $hrPosition = Position::query()->updateOrCreate(
            ['code' => 'HR-MANAGER'],
            ['department_id' => $hrDepartment->id, 'name' => 'HR Manager'],
        );

        $payrollPosition = Position::query()->updateOrCreate(
            ['code' => 'PAYROLL-ANALYST'],
            ['department_id' => $financeDepartment->id, 'name' => 'Payroll Analyst'],
        );

        $staffPosition = Position::query()->updateOrCreate(
            ['code' => 'STAFF'],
            ['department_id' => $developerDepartment->id, 'name' => 'Staff Employee'],
        );

        $mainOffice = AttendanceLocation::query()->updateOrCreate(
            ['code' => 'HQ'],
            [
                'name' => 'Main Office',
                'address' => 'Bonifacio Global City, Taguig',
                'latitude' => 14.5547291,
                'longitude' => 121.0244452,
                'radius_meters' => 100,
                'is_active' => true,
            ],
        );

        $workSchedule = WorkSchedule::query()->updateOrCreate(
            ['name' => 'Day Shift'],
            [
                'starts_at' => '08:00:00',
                'ends_at' => '17:00:00',
                'late_grace_minutes' => 10,
                'break_minutes' => 60,
                'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'is_flexible' => false,
            ],
        );

        $vacationLeave = LeaveType::query()->updateOrCreate(
            ['code' => 'VL'],
            ['name' => 'Vacation Leave', 'color' => '#0f766e', 'requires_balance' => true, 'default_days' => 10],
        );

        $sickLeave = LeaveType::query()->updateOrCreate(
            ['code' => 'SL'],
            ['name' => 'Sick Leave', 'color' => '#7c3aed', 'requires_balance' => true, 'default_days' => 10],
        );

        LeaveType::query()->updateOrCreate(
            ['code' => 'EL'],
            ['name' => 'Emergency Leave', 'color' => '#ea580c', 'requires_balance' => false, 'default_days' => 0],
        );

        $adminUser = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $roles['admin']->id,
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $hrUser = User::query()->updateOrCreate(
            ['email' => 'hr@example.com'],
            [
                'role_id' => $roles['hr']->id,
                'name' => 'HR Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $employeeUser = User::query()->updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'role_id' => $roles['employee']->id,
                'name' => 'Staff Employee',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $hrEmployee = Employee::query()->updateOrCreate(
            ['user_id' => $hrUser->id],
            [
                'employee_number' => 'EMP-2026-0001',
                'first_name' => 'Hannah',
                'last_name' => 'Rivera',
                'hire_date' => now()->subYears(3)->toDateString(),
                'department_id' => $hrDepartment->id,
                'position_id' => $hrPosition->id,
                'attendance_location_id' => $mainOffice->id,
                'work_schedule_id' => $workSchedule->id,
                'employment_status' => 'active',
                'employment_type' => 'regular',
                'base_salary' => 52000,
            ],
        );

        $employee = Employee::query()->updateOrCreate(
            ['user_id' => $employeeUser->id],
            [
                'employee_number' => 'EMP-2026-0002',
                'first_name' => 'Paolo',
                'last_name' => 'Santos',
                'hire_date' => now()->subYear()->toDateString(),
                'department_id' => $developerDepartment->id,
                'position_id' => $staffPosition->id,
                'manager_id' => $hrEmployee->id,
                'attendance_location_id' => $mainOffice->id,
                'work_schedule_id' => $workSchedule->id,
                'employment_status' => 'active',
                'employment_type' => 'regular',
                'base_salary' => 30000,
            ],
        );

        Employee::query()->updateOrCreate(
            ['user_id' => $adminUser->id],
            [
                'employee_number' => 'EMP-2026-0003',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'hire_date' => now()->subYears(4)->toDateString(),
                'department_id' => $financeDepartment->id,
                'position_id' => $payrollPosition->id,
                'attendance_location_id' => $mainOffice->id,
                'work_schedule_id' => $workSchedule->id,
                'employment_status' => 'active',
                'employment_type' => 'regular',
                'base_salary' => 60000,
            ],
        );

        foreach ([$vacationLeave, $sickLeave] as $leaveType) {
            LeaveBalance::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => now()->year,
                ],
                [
                    'allocated_days' => $leaveType->default_days,
                    'used_days' => 0,
                ],
            );
        }

        StatutoryDeductionTable::query()->updateOrCreate(
            ['type' => 'SSS', 'effective_from' => '2026-01-01'],
            ['rules' => ['brackets' => [['min' => 0, 'max' => null, 'employee_rate' => 0.045]]]],
        );

        StatutoryDeductionTable::query()->updateOrCreate(
            ['type' => 'PHILHEALTH', 'effective_from' => '2026-01-01'],
            ['rules' => ['brackets' => [['min' => 0, 'max' => null, 'employee_rate' => 0.025, 'cap' => 2500]]]],
        );

        StatutoryDeductionTable::query()->updateOrCreate(
            ['type' => 'PAGIBIG', 'effective_from' => '2026-01-01'],
            ['rules' => ['brackets' => [['min' => 0, 'max' => null, 'employee_rate' => 0.02, 'cap' => 100]]]],
        );

        StatutoryDeductionTable::query()->updateOrCreate(
            ['type' => 'TAX', 'effective_from' => '2026-01-01'],
            ['rules' => ['brackets' => [
                ['min' => 0, 'max' => 20833, 'employee_fixed' => 0],
                ['min' => 20833, 'max' => 33332, 'base' => 0, 'over' => 20833, 'rate' => 0.15],
                ['min' => 33333, 'max' => 66666, 'base' => 1875, 'over' => 33333, 'rate' => 0.20],
                ['min' => 66667, 'max' => null, 'base' => 8541.8, 'over' => 66667, 'rate' => 0.25],
            ]]],
        );
    }
}
