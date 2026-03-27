<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function view(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }
}
