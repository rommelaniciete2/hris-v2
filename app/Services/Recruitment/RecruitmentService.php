<?php

namespace App\Services\Recruitment;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\Employees\EmployeeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RecruitmentService
{
    public function __construct(
        public EmployeeService $employeeService,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createJobPosting(array $validated, User $creator): JobPosting
    {
        return JobPosting::query()->create([
            ...$validated,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function scheduleInterview(Applicant $applicant, array $validated): Interview
    {
        $interview = $applicant->interviews()->create([
            'interviewer_id' => $validated['interviewer_id'] ?? null,
            'scheduled_at' => CarbonImmutable::parse($validated['scheduled_at']),
            'status' => 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        $applicant->update(['stage' => 'interview_scheduled']);

        return $interview;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function hireApplicant(Applicant $applicant, array $validated): Applicant
    {
        DB::transaction(function () use ($applicant, $validated) {
            $employee = $this->employeeService->create([
                'email' => $validated['email'] ?? $applicant->email,
                'role_id' => $validated['role_id'],
                'employee_number' => $validated['employee_number'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'phone' => $validated['phone'] ?? $applicant->phone,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'hire_date' => $validated['hire_date'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'manager_id' => $validated['manager_id'] ?? null,
                'attendance_location_id' => $validated['attendance_location_id'],
                'work_schedule_id' => $validated['work_schedule_id'],
                'employment_status' => 'active',
                'employment_type' => $validated['employment_type'] ?? 'regular',
                'base_salary' => $validated['base_salary'],
                'notes' => $validated['notes'] ?? $applicant->notes,
            ]);

            $applicant->forceFill([
                'stage' => 'hired',
                'hired_employee_id' => $employee->id,
                'hired_at' => now(),
            ])->save();
        });

        return $applicant->fresh(['jobPosting', 'hiredEmployee.user']);
    }
}
