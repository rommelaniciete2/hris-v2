<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use Illuminate\Support\Collection;

class PerformanceRepository
{
    /**
     * @return Collection<int, PerformanceReviewCycle>
     */
    public function reviewCycles(): Collection
    {
        return PerformanceReviewCycle::query()->latest('start_date')->limit(5)->get();
    }

    /**
     * @return Collection<int, PerformanceReview>
     */
    public function recentReviews(): Collection
    {
        return PerformanceReview::query()
            ->with(['employee.user', 'reviewer', 'kpiScores'])
            ->latest()
            ->limit(10)
            ->get();
    }
}
