<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
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
            'work_date' => fake()->date(),
            'clock_in_at' => now()->setTime(8, 0),
            'clock_out_at' => now()->setTime(17, 0),
            'clock_in_latitude' => 14.5547291,
            'clock_in_longitude' => 121.0244452,
            'clock_out_latitude' => 14.5547291,
            'clock_out_longitude' => 121.0244452,
            'geofence_distance_meters' => 20,
            'geofence_status' => 'within_radius',
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
            'status' => 'present',
        ];
    }
}
