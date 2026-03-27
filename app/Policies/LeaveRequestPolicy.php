<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasEmployeeProfile() || $user->hasAnyHrisPermission(['leaves.view_any', 'leaves.manage']);
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasAnyHrisPermission(['leaves.view_any', 'leaves.manage'])
            || $leaveRequest->employee_id === $user->employee?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasEmployeeProfile();
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === 'pending'
            && $leaveRequest->employee_id === $user->employee?->id;
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->status === 'pending'
            && $leaveRequest->employee_id === $user->employee?->id;
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasHrisPermission('leaves.manage');
    }
}
