<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Repositories\Attendance\AttendanceRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        public AttendanceRepository $attendanceRepository,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Attendance::class);

        $request->user()->loadMissing('employee.attendanceLocation', 'employee.workSchedule');
        $employee = $request->user()->employee;
        $todayRecord = $employee ? $this->attendanceRepository->todayRecord($employee, now()->toImmutable()) : null;
        $logs = $employee ? $this->attendanceRepository->recentLogs($employee) : collect();

        return Inertia::render('hris/Attendance/Index', [
            'employee' => $employee ? [
                'id' => $employee->id,
                'full_name' => $employee->fullName(),
                'employee_number' => $employee->employee_number,
            ] : null,
            'location' => $employee?->attendanceLocation?->only(['id', 'name', 'address', 'radius_meters', 'latitude', 'longitude']),
            'schedule' => $employee?->workSchedule?->only(['id', 'name', 'starts_at', 'ends_at', 'late_grace_minutes']),
            'todayRecord' => $todayRecord ? [
                'id' => $todayRecord->id,
                'work_date' => $todayRecord->work_date?->toDateString(),
                'clock_in_at' => $todayRecord->clock_in_at?->toIso8601String(),
                'clock_out_at' => $todayRecord->clock_out_at?->toIso8601String(),
                'status' => $todayRecord->status,
                'late_minutes' => $todayRecord->late_minutes,
                'undertime_minutes' => $todayRecord->undertime_minutes,
                'overtime_minutes' => $todayRecord->overtime_minutes,
            ] : null,
            'logs' => $logs->map(fn ($attendance) => [
                'id' => $attendance->id,
                'work_date' => $attendance->work_date?->toDateString(),
                'clock_in_at' => $attendance->clock_in_at?->toIso8601String(),
                'clock_out_at' => $attendance->clock_out_at?->toIso8601String(),
                'status' => $attendance->status,
                'late_minutes' => $attendance->late_minutes,
                'undertime_minutes' => $attendance->undertime_minutes,
                'overtime_minutes' => $attendance->overtime_minutes,
            ])->values(),
        ]);
    }
}
