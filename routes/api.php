<?php

use App\Http\Controllers\Api\V1\ApplicantHireController;
use App\Http\Controllers\Api\V1\AttendancePunchController;
use App\Http\Controllers\Api\V1\LeaveApprovalController;
use App\Http\Controllers\Api\V1\PayrollRunController;
use App\Http\Controllers\Api\V1\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['web', 'auth', 'verified'])->group(function () {
    Route::post('attendance/punch', AttendancePunchController::class)->name('api.v1.attendance.punch');
    Route::post('leave/{leaveRequest}/approval', LeaveApprovalController::class)->name('api.v1.leave.approval');
    Route::post('payroll/run', PayrollRunController::class)->name('api.v1.payroll.run');
    Route::get('reports/export', ReportExportController::class)->name('api.v1.reports.export');
    Route::post('applicants/{applicant}/hire', ApplicantHireController::class)->name('api.v1.applicants.hire');
});
