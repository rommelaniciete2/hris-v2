<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyHrisPermission(['attendance.view_any', 'attendance.manage', 'attendance.punch'])
            || $user->hasEmployeeProfile();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->hasAnyHrisPermission(['attendance.view_any', 'attendance.manage'])
            || $attendance->employee_id === $user->employee?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyHrisPermission(['attendance.punch', 'attendance.manage']);
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->hasHrisPermission('attendance.manage');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasHrisPermission('attendance.manage');
    }
}
