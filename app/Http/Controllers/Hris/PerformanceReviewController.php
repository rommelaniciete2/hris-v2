<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePerformanceReviewRequest;
use App\Http\Requests\UpdatePerformanceReviewRequest;
use App\Models\PerformanceReview;
use App\Services\Performance\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PerformanceReviewController extends Controller
{
    public function __construct(
        public PerformanceService $performanceService,
    ) {}

    public function store(StorePerformanceReviewRequest $request): RedirectResponse
    {
        $this->authorize('create', PerformanceReview::class);

        $this->performanceService->create($request->validated(), $request->user());

        return back()->with('success', 'Performance review submitted successfully.');
    }

    public function show(PerformanceReview $performanceReview): JsonResponse
    {
        $this->authorize('view', $performanceReview);

        $performanceReview->load(['employee.user', 'reviewer', 'kpiScores', 'cycle']);

        return response()->json([
            'id' => $performanceReview->id,
            'employee' => $performanceReview->employee?->fullName(),
            'reviewer' => $performanceReview->reviewer?->name,
            'overall_score' => $performanceReview->overall_score,
            'summary' => $performanceReview->summary,
            'comments' => $performanceReview->comments,
            'kpis' => $performanceReview->kpiScores->map(fn ($score) => $score->only(['name', 'score', 'comments']))->values(),
        ]);
    }

    public function update(UpdatePerformanceReviewRequest $request, PerformanceReview $performanceReview): RedirectResponse
    {
        $this->authorize('update', $performanceReview);

        $performanceReview->update($request->validated());

        return back()->with('success', 'Performance review updated successfully.');
    }
}
