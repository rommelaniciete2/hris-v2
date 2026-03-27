<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null) {
            $user->loadMissing('role', 'employee');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'role' => $user?->role?->only(['id', 'name', 'slug']),
                'permissions' => $user?->role?->permissions ?? [],
                'employee_id' => $user?->employee?->id,
                'capabilities' => [
                    'employees' => $user?->hasAnyHrisPermission(['employees.view_any', 'employees.manage']) ?? false,
                    'attendance' => ($user?->hasAnyHrisPermission(['attendance.view_any', 'attendance.manage', 'attendance.punch']) ?? false) || $user?->hasEmployeeProfile() === true,
                    'leave' => ($user?->hasAnyHrisPermission(['leaves.view_any', 'leaves.manage']) ?? false) || $user?->hasEmployeeProfile() === true,
                    'payroll' => ($user?->hasAnyHrisPermission(['payroll.view_any', 'payroll.manage']) ?? false) || $user?->hasEmployeeProfile() === true,
                    'reports' => $user?->hasHrisPermission('reports.view') ?? false,
                    'recruitment' => $user?->hasHrisPermission('recruitment.manage') ?? false,
                    'performance' => ($user?->hasHrisPermission('performance.manage') ?? false) || $user?->hasEmployeeProfile() === true,
                    'documents' => ($user?->hasHrisPermission('documents.manage') ?? false) || $user?->hasEmployeeProfile() === true,
                ],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
