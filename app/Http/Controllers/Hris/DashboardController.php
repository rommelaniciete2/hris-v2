<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reportService): Response
    {
        return Inertia::render('hris/Dashboard', $reportService->dashboardSummary());
    }
}
