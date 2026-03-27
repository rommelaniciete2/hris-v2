<?php

namespace Tests\Feature\Hris;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_employee_can_submit_leave_and_hr_can_approve_it(): void
    {
        $employeeUser = User::query()->where('email', 'employee@example.com')->firstOrFail();
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();
        $leaveType = LeaveType::query()->where('code', 'VL')->firstOrFail();

        $this->actingAs($employeeUser)->post(route('hris.leave.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->startOfWeek()->toDateString(),
            'end_date' => now()->addWeek()->startOfWeek()->addDay()->toDateString(),
            'reason' => 'Family trip',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::query()->whereBelongsTo($employeeUser->employee)->firstOrFail();

        $this->actingAs($hrUser)
            ->postJson(route('api.v1.leave.approval', $leaveRequest), [
                'status' => 'approved',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $leaveRequest->refresh();

        $balance = LeaveBalance::query()
            ->where('employee_id', $employeeUser->employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->firstOrFail();

        $this->assertSame('approved', $leaveRequest->status);
        $this->assertSame(2.0, (float) $balance->used_days);
    }
}
