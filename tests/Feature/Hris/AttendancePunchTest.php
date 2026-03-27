<?php

namespace Tests\Feature\Hris;

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_employee_can_time_in_and_time_out_within_the_geofence(): void
    {
        $employeeUser = User::query()->where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employeeUser)
            ->postJson(route('api.v1.attendance.punch'), [
                'latitude' => 14.5547291,
                'longitude' => 121.0244452,
                'accuracy' => 15,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->actingAs($employeeUser)
            ->postJson(route('api.v1.attendance.punch'), [
                'latitude' => 14.5547291,
                'longitude' => 121.0244452,
                'accuracy' => 15,
            ])
            ->assertOk();

        $attendance = Attendance::query()->whereBelongsTo($employeeUser->employee)->firstOrFail();

        $this->assertNotNull($attendance->clock_in_at);
        $this->assertNotNull($attendance->clock_out_at);
        $this->assertSame('within_radius', $attendance->geofence_status);
    }

    public function test_employee_cannot_punch_when_outside_the_geofence(): void
    {
        $employeeUser = User::query()->where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employeeUser)
            ->postJson(route('api.v1.attendance.punch'), [
                'latitude' => 14.5605,
                'longitude' => 121.0360,
                'accuracy' => 10,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['location']);
    }
}
