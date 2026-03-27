<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Repositories\Documents\DocumentRepository;
use App\Repositories\Employees\EmployeeRepository;
use App\Repositories\Performance\PerformanceRepository;
use App\Repositories\Recruitment\RecruitmentRepository;
use App\Services\Employees\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        public EmployeeRepository $employeeRepository,
        public EmployeeService $employeeService,
        public RecruitmentRepository $recruitmentRepository,
        public PerformanceRepository $performanceRepository,
        public DocumentRepository $documentRepository,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        $search = $request->string('search')->toString();
        $employees = $this->employeeRepository
            ->paginatedDirectory($search)
            ->through(fn (Employee $employee) => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->fullName(),
                'email' => $employee->user?->email,
                'role' => $employee->user?->role?->name,
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
                'manager' => $employee->manager?->fullName(),
                'employment_status' => $employee->employment_status,
                'base_salary' => $employee->base_salary,
            ]);

        $supportingData = $this->employeeRepository->supportingData();

        return Inertia::render('hris/Employees/Index', [
            'filters' => [
                'search' => $search,
                'tab' => $request->string('tab')->toString() ?: 'directory',
            ],
            'employees' => $employees,
            'departments' => $supportingData['departments']->map(fn ($department) => $department->only(['id', 'name', 'code', 'parent_id', 'description']))->values(),
            'positions' => $supportingData['positions']->map(fn ($position) => $position->only(['id', 'name', 'code', 'department_id']))->values(),
            'roles' => $supportingData['roles']->map(fn ($role) => $role->only(['id', 'name', 'slug']))->values(),
            'employeeOptions' => $supportingData['employeeOptions']->map(fn ($employeeOption) => [
                'id' => $employeeOption->id,
                'full_name' => $employeeOption->fullName(),
                'email' => $employeeOption->user?->email,
            ])->values(),
            'attendanceLocations' => $supportingData['attendanceLocations']->map(fn ($location) => $location->only(['id', 'name', 'code', 'address', 'radius_meters']))->values(),
            'workSchedules' => $supportingData['workSchedules']->map(fn ($schedule) => $schedule->only(['id', 'name', 'starts_at', 'ends_at']))->values(),
            'jobPostings' => $supportingData['jobPostings']->map(fn ($jobPosting) => [
                'id' => $jobPosting->id,
                'title' => $jobPosting->title,
                'status' => $jobPosting->status,
                'department' => $jobPosting->department?->name,
                'position' => $jobPosting->position?->name,
                'closes_at' => $jobPosting->closes_at?->toDateString(),
            ])->values(),
            'applicants' => $supportingData['applicants']->map(fn ($applicant) => [
                'id' => $applicant->id,
                'full_name' => $applicant->full_name,
                'email' => $applicant->email,
                'stage' => $applicant->stage,
                'job_posting' => $applicant->jobPosting?->title,
            ])->values(),
            'performanceReviews' => $supportingData['performanceReviews']->map(fn ($review) => [
                'id' => $review->id,
                'employee' => $review->employee?->fullName(),
                'reviewer' => $review->reviewer?->name,
                'overall_score' => $review->overall_score,
                'status' => $review->status,
            ])->values(),
            'reviewCycles' => $this->performanceRepository->reviewCycles()->map(fn ($cycle) => [
                'id' => $cycle->id,
                'name' => $cycle->name,
                'status' => $cycle->status,
            ])->values(),
            'upcomingInterviews' => $this->recruitmentRepository->upcomingInterviews()->map(fn ($interview) => [
                'id' => $interview->id,
                'applicant' => $interview->applicant?->full_name,
                'interviewer' => $interview->interviewer?->name,
                'scheduled_at' => $interview->scheduled_at?->toIso8601String(),
                'status' => $interview->status,
            ])->values(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return to_route('hris.employees.index');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $this->employeeService->create($request->validated());

        return to_route('hris.employees.show', $employee)->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee): Response
    {
        $this->authorize('view', $employee);

        $employee->load(['user.role', 'department', 'position', 'manager.user', 'attendanceLocation', 'workSchedule']);
        $supportingData = $this->employeeRepository->supportingData();

        return Inertia::render('hris/Employees/Show', [
            'employee' => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'middle_name' => $employee->middle_name,
                'full_name' => $employee->fullName(),
                'email' => $employee->user?->email,
                'role_id' => $employee->user?->role_id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'manager_id' => $employee->manager_id,
                'department' => $employee->department?->name,
                'position' => $employee->position?->name,
                'manager' => $employee->manager?->fullName(),
                'employment_status' => $employee->employment_status,
                'employment_type' => $employee->employment_type,
                'base_salary' => $employee->base_salary,
                'hire_date' => $employee->hire_date?->toDateString(),
                'birth_date' => $employee->birth_date?->toDateString(),
                'gender' => $employee->gender,
                'marital_status' => $employee->marital_status,
                'phone' => $employee->phone,
                'address' => $employee->address,
                'emergency_contact_name' => $employee->emergency_contact_name,
                'emergency_contact_phone' => $employee->emergency_contact_phone,
                'attendance_location_id' => $employee->attendance_location_id,
                'work_schedule_id' => $employee->work_schedule_id,
                'attendance_location' => $employee->attendanceLocation?->name,
                'work_schedule' => $employee->workSchedule?->name,
                'notes' => $employee->notes,
            ],
            'roles' => $supportingData['roles']->map(fn ($role) => $role->only(['id', 'name', 'slug']))->values(),
            'departments' => $supportingData['departments']->map(fn ($department) => $department->only(['id', 'name', 'code']))->values(),
            'positions' => $supportingData['positions']->map(fn ($position) => $position->only(['id', 'name', 'code', 'department_id']))->values(),
            'employeeOptions' => $supportingData['employeeOptions']
                ->reject(fn (Employee $employeeOption) => $employeeOption->is($employee))
                ->map(fn (Employee $employeeOption) => [
                    'id' => $employeeOption->id,
                    'full_name' => $employeeOption->fullName(),
                    'email' => $employeeOption->user?->email,
                ])->values(),
            'attendanceLocations' => $supportingData['attendanceLocations']->map(fn ($location) => $location->only(['id', 'name', 'code', 'address', 'radius_meters']))->values(),
            'workSchedules' => $supportingData['workSchedules']->map(fn ($schedule) => $schedule->only(['id', 'name', 'starts_at', 'ends_at']))->values(),
            'documents' => $this->documentRepository->forEmployee($employee)->map(fn ($document) => [
                'id' => $document->id,
                'category' => $document->category,
                'name' => $document->name,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
            ])->values(),
            'attendanceLogs' => Attendance::query()
                ->whereBelongsTo($employee)
                ->latest('work_date')
                ->limit(10)
                ->get()
                ->map(fn ($attendance) => [
                    'id' => $attendance->id,
                    'work_date' => $attendance->work_date?->toDateString(),
                    'status' => $attendance->status,
                    'late_minutes' => $attendance->late_minutes,
                    'undertime_minutes' => $attendance->undertime_minutes,
                    'overtime_minutes' => $attendance->overtime_minutes,
                ])->values(),
            'payrolls' => Payroll::query()
                ->whereBelongsTo($employee)
                ->latest('period_end')
                ->limit(6)
                ->get()
                ->map(fn ($payroll) => [
                    'id' => $payroll->id,
                    'period_start' => $payroll->period_start?->toDateString(),
                    'period_end' => $payroll->period_end?->toDateString(),
                    'net_pay' => $payroll->net_pay,
                    'status' => $payroll->status,
                ])->values(),
            'leaveBalances' => LeaveBalance::query()
                ->with('leaveType')
                ->whereBelongsTo($employee)
                ->latest('year')
                ->get()
                ->map(fn ($balance) => [
                    'id' => $balance->id,
                    'leave_type' => $balance->leaveType?->name,
                    'allocated_days' => $balance->allocated_days,
                    'used_days' => $balance->used_days,
                    'remaining_days' => $balance->remainingDays(),
                    'year' => $balance->year,
                ])->values(),
            'leaveHistory' => LeaveRequest::query()
                ->with('leaveType')
                ->whereBelongsTo($employee)
                ->latest('start_date')
                ->limit(10)
                ->get()
                ->map(fn ($leave) => [
                    'id' => $leave->id,
                    'leave_type' => $leave->leaveType?->name,
                    'start_date' => $leave->start_date?->toDateString(),
                    'end_date' => $leave->end_date?->toDateString(),
                    'days' => $leave->days,
                    'status' => $leave->status,
                ])->values(),
        ]);
    }

    public function edit(Employee $employee): RedirectResponse
    {
        return to_route('hris.employees.show', $employee);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee = $this->employeeService->update($employee, $request->validated(), $request->user());

        return to_route('hris.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->delete($employee);

        return to_route('hris.employees.index')->with('success', 'Employee deleted successfully.');
    }
}
