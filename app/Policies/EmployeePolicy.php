<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyHrisPermission(['employees.view_any', 'employees.manage']);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasAnyHrisPermission(['employees.view_any', 'employees.manage'])
            || $user->employee?->is($employee);
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('employees.manage');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasHrisPermission('employees.manage')
            || $user->employee?->is($employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasHrisPermission('employees.manage')
            && ! $user->employee?->is($employee);
    }
}
