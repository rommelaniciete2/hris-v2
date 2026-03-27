<?php

namespace Tests\Feature\Hris;

use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Interview;
use App\Models\Payroll;
use App\Models\PerformanceReview;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\HrisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HrisSeeder::class);
    }

    public function test_dashboard_summary_props_are_frontend_safe(): void
    {
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();

        $this->createSummaryFixtures($hrUser);

        $response = $this->actingAs($hrUser)->get(route('dashboard'));

        $this->assertSummaryResponse($response, 'hris/Dashboard');
    }

    public function test_reports_summary_props_are_frontend_safe(): void
    {
        $hrUser = User::query()->where('email', 'hr@example.com')->firstOrFail();

        $this->createSummaryFixtures($hrUser);

        $response = $this->actingAs($hrUser)->get(route('hris.reports.index'));

        $this->assertSummaryResponse($response, 'hris/Reports/Index');
    }

    private function createSummaryFixtures(User $hrUser): void
    {
        $employee = $hrUser->employee;

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'work_date' => '2026-03-27',
            'status' => 'present',
            'late_minutes' => 5,
            'overtime_minutes' => 30,
        ]);

        Payroll::factory()->create([
            'employee_id' => $employee->id,
            'processed_by' => $hrUser->id,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-15',
            'status' => 'processed',
            'net_pay' => '21325.80',
        ]);

        PerformanceReview::factory()->create([
            'employee_id' => $employee->id,
            'reviewer_id' => $hrUser->id,
            'status' => 'submitted',
            'overall_score' => '4.25',
        ]);

        $applicant = Applicant::factory()->create([
            'full_name' => 'Jamie Cruz',
            'stage' => 'interviewed',
        ]);

        Interview::factory()->create([
            'applicant_id' => $applicant->id,
            'interviewer_id' => $hrUser->id,
            'scheduled_at' => CarbonImmutable::parse('2026-04-02 09:30:00'),
            'status' => 'scheduled',
        ]);
    }

    private function assertSummaryResponse(
        TestResponse $response,
        string $component,
    ): void {
        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->where('attendance.0.work_date', '2026-03-27')
                ->where('attendance.0.status', 'present')
                ->where('payrolls.0.employee', 'Hannah Rivera')
                ->where('payrolls.0.period_start', '2026-03-01')
                ->where('payrolls.0.period_end', '2026-03-15')
                ->where('payrolls.0.status', 'processed')
                ->where('reviews.0.employee', 'Hannah Rivera')
                ->where('reviews.0.reviewer', 'HR Manager')
                ->where('interviews.0.applicant', 'Jamie Cruz')
                ->where('interviews.0.interviewer', 'HR Manager')
                ->where('interviews.0.scheduled_at', 'Apr 2, 2026 9:30 AM')
                ->where('interviews.0.status', 'scheduled'),
            );
    }
}
