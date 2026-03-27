<?php

namespace Database\Factories;

use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkSchedule>
 */
class WorkScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Day Shift',
            'starts_at' => '08:00:00',
            'ends_at' => '17:00:00',
            'late_grace_minutes' => 10,
            'break_minutes' => 60,
            'weekdays' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'is_flexible' => false,
        ];
    }
}
