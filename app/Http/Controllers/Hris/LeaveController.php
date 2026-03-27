<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Repositories\Leaves\LeaveRepository;
use App\Services\Leaves\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function __construct(
        public LeaveRepository $leaveRepository,
        public LeaveService $leaveService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $user = $request->user();
        $employee = $user->employee;
        $balances = $employee !== null
            ? $this->leaveRepository->balances($employee, $this->leaveRepository->currentYear())
            : collect();

        return Inertia::render('hris/Leaves/Index', [
            'leaveTypes' => LeaveType::query()->orderBy('name')->get()->map(fn ($leaveType) => $leaveType->only(['id', 'name', 'code', 'color']))->values(),
            'balances' => $balances->map(fn ($balance) => [
                'id' => $balance->id,
                'leave_type' => $balance->leaveType?->name,
                'allocated_days' => $balance->allocated_days,
                'used_days' => $balance->used_days,
                'remaining_days' => $balance->remainingDays(),
            ])->values(),
            'history' => $this->leaveRepository->historyFor($user)->map(fn ($leave) => [
                'id' => $leave->id,
                'employee' => $leave->employee?->fullName(),
                'leave_type' => $leave->leaveType?->name,
                'start_date' => $leave->start_date?->toDateString(),
                'end_date' => $leave->end_date?->toDateString(),
                'days' => $leave->days,
                'status' => $leave->status,
                'rejection_reason' => $leave->rejection_reason,
            ])->values(),
            'pendingApprovals' => $user->hasHrisPermission('leaves.manage')
                ? $this->leaveRepository->pendingApprovals()->map(fn ($leave) => [
                    'id' => $leave->id,
                    'employee' => $leave->employee?->fullName(),
                    'leave_type' => $leave->leaveType?->name,
                    'start_date' => $leave->start_date?->toDateString(),
                    'end_date' => $leave->end_date?->toDateString(),
                    'days' => $leave->days,
                ])->values()
                : [],
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $this->leaveService->submit($request->user(), $request->validated());

        return back()->with('success', 'Leave request submitted successfully.');
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        $leaveRequest->update($request->validated());

        return back()->with('success', 'Leave request updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Leave request cancelled successfully.');
    }
}
