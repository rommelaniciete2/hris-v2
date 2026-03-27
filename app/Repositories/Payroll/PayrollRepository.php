<?php

namespace App\Repositories\Payroll;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollRepository
{
    public function paginatedFor(User $user): LengthAwarePaginator
    {
        return Payroll::query()
            ->with(['employee.user', 'processedBy'])
            ->when(! $user->hasAnyHrisPermission(['payroll.view_any', 'payroll.manage']), function ($query) use ($user) {
                $query->where('employee_id', $user->employee?->id);
            })
            ->latest('period_end')
            ->paginate(10);
    }
}
