<?php

namespace App\Services\Employees;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\InvitationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(
        public InvitationService $invitationService,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Employee
    {
        /** @var Employee $employee */
        $employee = DB::transaction(function () use ($validated) {
            $role = Role::query()
                ->when(isset($validated['role_id']), fn ($query) => $query->whereKey($validated['role_id']))
                ->first()
                ?? Role::query()->where('slug', 'employee')->firstOrFail();

            $employeeNumber = $validated['employee_number'] ?? $this->generateEmployeeNumber();
            $fullName = trim($validated['first_name'].' '.($validated['last_name'] ?? ''));

            $user = User::query()->create([
                'role_id' => $role->id,
                'name' => $fullName,
                'email' => $validated['email'],
                'password' => Str::password(24),
            ]);

            $employee = new Employee(collect($validated)->except(['email', 'role_id'])->all());
            $employee->employee_number = $employeeNumber;
            $employee->user()->associate($user);
            $employee->save();

            return $employee->fresh(['user.role', 'department', 'position', 'manager', 'attendanceLocation', 'workSchedule']);
        });

        $this->invitationService->sendResetLink($employee->user);

        return $employee;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Employee $employee, array $validated, User $actor): Employee
    {
        $employeeAttributes = collect($validated)->except(['email', 'role_id'])->all();

        if (! $actor->hasHrisPermission('employees.manage')) {
            $employeeAttributes = Arr::only($employeeAttributes, [
                'phone',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        }

        DB::transaction(function () use ($actor, $employee, $employeeAttributes, $validated) {
            if ($actor->hasHrisPermission('employees.manage')) {
                $employee->user->forceFill([
                    'role_id' => $validated['role_id'],
                    'name' => trim($validated['first_name'].' '.$validated['last_name']),
                    'email' => $validated['email'],
                ])->save();
            }

            $employee->fill($employeeAttributes);
            $employee->save();
        });

        return $employee->fresh(['user.role', 'department', 'position', 'manager', 'attendanceLocation', 'workSchedule']);
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            $user?->delete();
        });
    }

    private function generateEmployeeNumber(): string
    {
        return 'EMP-'.now()->format('Y').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
