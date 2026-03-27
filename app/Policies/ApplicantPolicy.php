<?php

namespace App\Policies;

use App\Models\Applicant;
use App\Models\User;

class ApplicantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function view(User $user, Applicant $applicant): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function update(User $user, Applicant $applicant): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }

    public function delete(User $user, Applicant $applicant): bool
    {
        return $user->hasHrisPermission('recruitment.manage');
    }
}
