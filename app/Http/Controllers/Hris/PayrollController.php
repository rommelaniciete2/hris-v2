<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Repositories\Payroll\PayrollRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function __construct(
        public PayrollRepository $payrollRepository,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payroll::class);

        $today = CarbonImmutable::today();
        $isFirstCutoff = $today->day <= 15;

        return Inertia::render('hris/Payroll/Index', [
            'payrolls' => $this->payrollRepository->paginatedFor($request->user())->through(fn (Payroll $payroll) => [
                'id' => $payroll->id,
                'employee' => $payroll->employee?->fullName(),
                'period_start' => $payroll->period_start?->toDateString(),
                'period_end' => $payroll->period_end?->toDateString(),
                'pay_date' => $payroll->pay_date?->toDateString(),
                'status' => $payroll->status,
                'gross_pay' => $payroll->gross_pay,
                'total_deductions' => $payroll->total_deductions,
                'net_pay' => $payroll->net_pay,
            ]),
            'defaultRun' => [
                'period_start' => $isFirstCutoff ? $today->startOfMonth()->toDateString() : $today->startOfMonth()->addDays(15)->toDateString(),
                'period_end' => $isFirstCutoff ? $today->startOfMonth()->addDays(14)->toDateString() : $today->endOfMonth()->toDateString(),
                'pay_date' => $today->toDateString(),
            ],
        ]);
    }

    public function show(Payroll $payroll): Response
    {
        $this->authorize('view', $payroll);

        $payroll->load(['employee.user', 'processedBy']);

        return Inertia::render('hris/Payroll/Show', [
            'payroll' => [
                'id' => $payroll->id,
                'employee' => $payroll->employee?->fullName(),
                'employee_number' => $payroll->employee?->employee_number,
                'period_start' => $payroll->period_start?->toDateString(),
                'period_end' => $payroll->period_end?->toDateString(),
                'pay_date' => $payroll->pay_date?->toDateString(),
                'status' => $payroll->status,
                'earnings_breakdown' => $payroll->earnings_breakdown,
                'deductions_breakdown' => $payroll->deductions_breakdown,
                'gross_pay' => $payroll->gross_pay,
                'total_deductions' => $payroll->total_deductions,
                'net_pay' => $payroll->net_pay,
            ],
        ]);
    }
}
