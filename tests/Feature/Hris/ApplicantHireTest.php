<?php

namespace Tests\Feature\Hris;

use App\Models\Applicant;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantHireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_hr_can_convert_an_applicant_to_an_employee(): void
    {
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();
        $role = Role::query()->where('slug', 'employee')->firstOrFail();

        $applicant = Applicant::factory()->create([
            'full_name' => 'Casey Dela Cruz',
            'email' => 'casey@example.com',
            'stage' => 'interviewed',
        ]);

        $response = $this->actingAs($hrUser)
            ->postJson(route('api.v1.applicants.hire', $applicant), [
                'role_id' => $role->id,
                'employee_number' => 'EMP-2026-0900',
                'email' => 'casey@example.com',
                'first_name' => 'Casey',
                'last_name' => 'Dela Cruz',
                'hire_date' => now()->toDateString(),
                'department_id' => $hrUser->employee->department_id,
                'position_id' => $hrUser->employee->position_id,
                'attendance_location_id' => $hrUser->employee->attendance_location_id,
                'work_schedule_id' => $hrUser->employee->work_schedule_id,
                'employment_type' => 'regular',
                'base_salary' => 27000,
            ]);

        $response->assertOk();

        $applicant->refresh();

        $this->assertSame('hired', $applicant->stage);
        $this->assertNotNull($applicant->hired_employee_id);
        $this->assertDatabaseHas('employees', [
            'id' => $applicant->hired_employee_id,
            'employee_number' => 'EMP-2026-0900',
        ]);
    }
}
