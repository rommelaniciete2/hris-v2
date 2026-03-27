<?php

namespace Tests\Feature\Hris;

use App\Models\User;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_hr_can_create_an_employee_record(): void
    {
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();

        $response = $this->actingAs($hrUser)->post(route('hris.employees.store'), [
            'role_id' => User::query()->where('email', 'employee@example.com')->firstOrFail()->role_id,
            'email' => 'new.employee@example.com',
            'first_name' => 'Jamie',
            'last_name' => 'Cruz',
            'hire_date' => now()->toDateString(),
            'department_id' => $hrUser->employee->department_id,
            'position_id' => $hrUser->employee->position_id,
            'manager_id' => $hrUser->employee->id,
            'attendance_location_id' => $hrUser->employee->attendance_location_id,
            'work_schedule_id' => $hrUser->employee->work_schedule_id,
            'employment_status' => 'active',
            'employment_type' => 'regular',
            'base_salary' => 28000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'new.employee@example.com',
        ]);

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Jamie',
            'last_name' => 'Cruz',
            'employment_status' => 'active',
        ]);
    }

    public function test_employee_cannot_open_reports_page(): void
    {
        $employeeUser = User::query()->where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employeeUser)
            ->get(route('hris.reports.index'))
            ->assertForbidden();
    }
}
