<?php

namespace App\Services\Reports;

use App\Repositories\Recruitment\RecruitmentRepository;
use App\Repositories\Reports\ReportRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function __construct(
        public ReportRepository $reportRepository,
        public RecruitmentRepository $recruitmentRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardSummary(): array
    {
        return [
            'cards' => $this->reportRepository->summaryCards(),
            'attendance' => $this->reportRepository->recentAttendance()
                ->map(fn ($attendance) => [
                    'id' => $attendance->id,
                    'work_date' => $attendance->work_date?->toDateString(),
                    'status' => $attendance->status,
                    'late_minutes' => $attendance->late_minutes,
                    'undertime_minutes' => $attendance->undertime_minutes,
                    'overtime_minutes' => $attendance->overtime_minutes,
                ])
                ->values()
                ->all(),
            'payrolls' => $this->reportRepository->recentPayrolls()
                ->map(fn ($payroll) => [
                    'id' => $payroll->id,
                    'employee' => $payroll->employee?->fullName(),
                    'period_start' => $payroll->period_start?->toDateString(),
                    'period_end' => $payroll->period_end?->toDateString(),
                    'status' => $payroll->status,
                    'net_pay' => $payroll->net_pay,
                ])
                ->values()
                ->all(),
            'reviews' => $this->reportRepository->recentPerformanceReviews()
                ->map(fn ($review) => [
                    'id' => $review->id,
                    'employee' => $review->employee?->fullName(),
                    'reviewer' => $review->reviewer?->name,
                    'overall_score' => $review->overall_score,
                    'status' => $review->status,
                ])
                ->values()
                ->all(),
            'interviews' => $this->recruitmentRepository->upcomingInterviews()
                ->map(fn ($interview) => [
                    'id' => $interview->id,
                    'applicant' => $interview->applicant?->full_name,
                    'interviewer' => $interview->interviewer?->name,
                    'scheduled_at' => $interview->scheduled_at?->format('M j, Y g:i A'),
                    'status' => $interview->status,
                ])
                ->values()
                ->all(),
        ];
    }

    public function export(string $type): StreamedResponse
    {
        $rows = match ($type) {
            'attendance' => $this->reportRepository->recentAttendance()->map(fn ($attendance) => [
                'Employee' => $attendance->employee->fullName(),
                'Date' => $attendance->work_date?->toDateString(),
                'Status' => $attendance->status,
                'Late Minutes' => $attendance->late_minutes,
                'Undertime Minutes' => $attendance->undertime_minutes,
                'Overtime Minutes' => $attendance->overtime_minutes,
            ]),
            'payroll' => $this->reportRepository->recentPayrolls()->map(fn ($payroll) => [
                'Employee' => $payroll->employee->fullName(),
                'Period Start' => $payroll->period_start?->toDateString(),
                'Period End' => $payroll->period_end?->toDateString(),
                'Net Pay' => $payroll->net_pay,
                'Status' => $payroll->status,
            ]),
            'employees' => $this->reportRepository->recentEmployees()->map(fn ($employee) => [
                'Employee Number' => $employee->employee_number,
                'Employee' => $employee->fullName(),
                'Department' => $employee->department?->name,
                'Position' => $employee->position?->name,
                'Status' => $employee->employment_status,
            ]),
            default => collect(),
        };

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            $firstRow = $rows->first();

            if ($firstRow !== null) {
                fputcsv($handle, array_keys($firstRow));

                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row));
                }
            }

            fclose($handle);
        }, "{$type}-report.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }
}
