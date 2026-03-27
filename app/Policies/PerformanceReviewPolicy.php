<?php

namespace App\Policies;

use App\Models\PerformanceReview;
use App\Models\User;

class PerformanceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasEmployeeProfile() || $user->hasHrisPermission('performance.manage');
    }

    public function view(User $user, PerformanceReview $performanceReview): bool
    {
        return $user->hasHrisPermission('performance.manage')
            || $performanceReview->employee_id === $user->employee?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('performance.manage')
            || $user->employee?->directReports()->exists() === true;
    }

    public function update(User $user, PerformanceReview $performanceReview): bool
    {
        return $user->hasHrisPermission('performance.manage')
            || $performanceReview->reviewer_id === $user->id;
    }

    public function delete(User $user, PerformanceReview $performanceReview): bool
    {
        return $user->hasHrisPermission('performance.manage');
    }
}
