<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\User;
use App\Repositories\Attendance\AttendanceRepository;
use App\Support\Hris\AttendanceCalculator;
use App\Support\Hris\DistanceCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        public AttendanceRepository $attendanceRepository,
        public DistanceCalculator $distanceCalculator,
        public AttendanceCalculator $attendanceCalculator,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function punch(User $user, array $validated): Attendance
    {
        $user->loadMissing('employee.attendanceLocation', 'employee.workSchedule');

        $employee = $user->employee;
        $location = $employee?->attendanceLocation;
        $schedule = $employee?->workSchedule;

        if ($employee === null || $location === null || $schedule === null) {
            throw ValidationException::withMessages([
                'location' => 'Your employee profile is missing an attendance location or work schedule.',
            ]);
        }

        $accuracy = (int) ($validated['accuracy'] ?? 0);

        if ($accuracy > config('hris.attendance.minimum_accuracy_meters')) {
            throw ValidationException::withMessages([
                'accuracy' => 'GPS accuracy is too low. Please move to an open area and try again.',
            ]);
        }

        $distance = $this->distanceCalculator->inMeters(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $location->latitude,
            (float) $location->longitude,
        );

        if ($distance > $location->radius_meters) {
            throw ValidationException::withMessages([
                'location' => 'You are outside the allowed attendance radius.',
            ]);
        }

        $today = CarbonImmutable::today();
        $now = CarbonImmutable::now();

        return DB::transaction(function () use ($employee, $today, $now, $validated, $distance, $schedule) {
            $attendance = $this->attendanceRepository->todayRecord($employee, $today)
                ?? new Attendance([
                    'employee_id' => $employee->id,
                    'work_date' => $today,
                ]);

            if ($attendance->clock_in_at === null) {
                $attendance->fill([
                    'clock_in_at' => $now,
                    'clock_in_latitude' => $validated['latitude'],
                    'clock_in_longitude' => $validated['longitude'],
                    'geofence_distance_meters' => $distance,
                    'geofence_status' => 'within_radius',
                    'status' => 'in_progress',
                ])->save();

                $summary = $this->attendanceCalculator->summarize($today, $now, null, $schedule);
                $attendance->fill($summary)->save();

                return $attendance->fresh();
            }

            if ($attendance->clock_out_at !== null) {
                throw ValidationException::withMessages([
                    'attendance' => 'You have already completed your attendance for today.',
                ]);
            }

            $summary = $this->attendanceCalculator->summarize(
                $today,
                CarbonImmutable::instance($attendance->clock_in_at),
                $now,
                $schedule,
            );

            $attendance->fill([
                'clock_out_at' => $now,
                'clock_out_latitude' => $validated['latitude'],
                'clock_out_longitude' => $validated['longitude'],
                'geofence_distance_meters' => $distance,
                'geofence_status' => 'within_radius',
                ...$summary,
            ])->save();

            return $attendance->fresh();
        });
    }
}
