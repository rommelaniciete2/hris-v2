<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendancePunchRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\Attendance\AttendanceService;

class AttendancePunchController extends Controller
{
    public function __invoke(AttendancePunchRequest $request, AttendanceService $attendanceService): AttendanceResource
    {
        $attendance = $attendanceService->punch($request->user(), $request->validated());

        return new AttendanceResource($attendance);
    }
}
