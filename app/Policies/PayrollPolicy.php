<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasEmployeeProfile() || $user->hasAnyHrisPermission(['payroll.view_any', 'payroll.manage']);
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->hasAnyHrisPermission(['payroll.view_any', 'payroll.manage'])
            || $payroll->employee_id === $user->employee?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasHrisPermission('payroll.manage');
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->hasHrisPermission('payroll.manage');
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->hasHrisPermission('payroll.manage');
    }
}
