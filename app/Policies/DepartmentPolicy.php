<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyHrisPermission(['employees.view_any', 'organization.manage']);
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasAnyHrisPermission(['employees.view_any', 'organization.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('organization.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasHrisPermission('organization.manage');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasHrisPermission('organization.manage');
    }
}
