<?php

namespace App\Services\Performance;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, User $reviewer): PerformanceReview
    {
        /** @var PerformanceReview $review */
        $review = DB::transaction(function () use ($validated, $reviewer) {
            $review = PerformanceReview::query()->create([
                'performance_review_cycle_id' => $validated['performance_review_cycle_id'],
                'employee_id' => $validated['employee_id'],
                'reviewer_id' => $reviewer->id,
                'status' => 'submitted',
                'summary' => $validated['summary'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'submitted_at' => now(),
            ]);

            $scores = collect($validated['kpis'] ?? [])->map(function (array $kpi) use ($review) {
                return $review->kpiScores()->create([
                    'name' => $kpi['name'],
                    'score' => $kpi['score'],
                    'comments' => $kpi['comments'] ?? null,
                ]);
            });

            $review->update([
                'overall_score' => round((float) $scores->avg('score'), 2),
            ]);

            return $review;
        });

        return $review->fresh(['employee.user', 'reviewer', 'kpiScores', 'cycle']);
    }
}
