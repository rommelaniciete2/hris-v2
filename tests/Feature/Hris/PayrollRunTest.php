<?php

namespace Tests\Feature\Hris;

use App\Models\Payroll;
use App\Models\User;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollRunTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_hr_can_run_payroll_and_employee_can_view_their_payslip(): void
    {
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();
        $employeeUser = User::query()->where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($hrUser)
            ->postJson(route('api.v1.payroll.run'), [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->startOfMonth()->addDays(14)->toDateString(),
                'pay_date' => now()->startOfMonth()->addDays(15)->toDateString(),
            ])
            ->assertOk();

        $payroll = Payroll::query()->whereBelongsTo($employeeUser->employee)->firstOrFail();

        $this->assertSame('processed', $payroll->status);
        $this->assertGreaterThan(0, (float) $payroll->gross_pay);

        $this->actingAs($employeeUser)
            ->get(route('hris.payroll.show', $payroll))
            ->assertOk();
    }
}
