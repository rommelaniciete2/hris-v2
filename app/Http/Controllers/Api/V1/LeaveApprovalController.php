<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveApprovalRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\Leaves\LeaveService;

class LeaveApprovalController extends Controller
{
    public function __invoke(
        LeaveApprovalRequest $request,
        LeaveRequest $leaveRequest,
        LeaveService $leaveService,
    ): LeaveRequestResource {
        $this->authorize('approve', $leaveRequest);

        return new LeaveRequestResource(
            $leaveService->approve(
                $leaveRequest,
                $request->user(),
                $request->string('status')->toString(),
                $request->string('rejection_reason')->toString() ?: null,
            ),
        );
    }
}
