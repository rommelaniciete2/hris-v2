<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportService $reportService): Response
    {
        abort_unless($request->user()?->hasHrisPermission('reports.view'), 403);

        return Inertia::render('hris/Reports/Index', $reportService->dashboardSummary());
    }
}
