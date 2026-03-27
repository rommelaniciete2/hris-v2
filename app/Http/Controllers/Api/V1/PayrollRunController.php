<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRunRequest;
use App\Http\Resources\PayrollResource;
use App\Models\Payroll;
use App\Services\Payroll\PayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PayrollRunController extends Controller
{
    public function __invoke(PayrollRunRequest $request, PayrollService $payrollService): AnonymousResourceCollection
    {
        $this->authorize('create', Payroll::class);

        $payrolls = $payrollService->run(
            CarbonImmutable::parse($request->string('period_start')->toString()),
            CarbonImmutable::parse($request->string('period_end')->toString()),
            CarbonImmutable::parse($request->string('pay_date')->toString()),
            $request->user(),
        );

        return PayrollResource::collection($payrolls);
    }
}
