<?php

namespace App\Repositories\Employees;

use App\Models\Applicant;
use App\Models\AttendanceLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosting;
use App\Models\PerformanceReview;
use App\Models\Position;
use App\Models\Role;
use App\Models\WorkSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeRepository
{
    public function paginatedDirectory(?string $search = null): LengthAwarePaginator
    {
        return Employee::query()
            ->with(['user.role', 'department', 'position', 'manager', 'attendanceLocation'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function supportingData(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'employeeOptions' => Employee::query()
                ->with('user')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'attendanceLocations' => AttendanceLocation::query()->where('is_active', true)->orderBy('name')->get(),
            'workSchedules' => WorkSchedule::query()->orderBy('name')->get(),
            'jobPostings' => JobPosting::query()->with(['department', 'position'])->latest()->limit(6)->get(),
            'applicants' => Applicant::query()->with('jobPosting')->latest()->limit(6)->get(),
            'performanceReviews' => PerformanceReview::query()
                ->with(['employee.user', 'reviewer'])
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }
}
