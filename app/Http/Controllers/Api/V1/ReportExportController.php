<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportExportRequest;
use App\Services\Reports\ReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(ReportExportRequest $request, ReportService $reportService): StreamedResponse
    {
        return $reportService->export($request->string('type')->toString());
    }
}
