<?php

use App\Http\Controllers\Hris\ApplicantController;
use App\Http\Controllers\Hris\AttendanceController;
use App\Http\Controllers\Hris\DashboardController;
use App\Http\Controllers\Hris\DepartmentController;
use App\Http\Controllers\Hris\DocumentController;
use App\Http\Controllers\Hris\EmployeeController;
use App\Http\Controllers\Hris\JobPostingController;
use App\Http\Controllers\Hris\LeaveController;
use App\Http\Controllers\Hris\PayrollController;
use App\Http\Controllers\Hris\PerformanceReviewController;
use App\Http\Controllers\Hris\ReportController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('hris')->name('hris.')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
        Route::put('leave/{leaveRequest}', [LeaveController::class, 'update'])->name('leave.update');
        Route::delete('leave/{leaveRequest}', [LeaveController::class, 'destroy'])->name('leave.destroy');
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('reports', ReportController::class)->name('reports.index');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::post('applicants', [ApplicantController::class, 'store'])->name('applicants.store');
        Route::put('applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');
        Route::delete('applicants/{applicant}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');
        Route::post('job-postings', [JobPostingController::class, 'store'])->name('job-postings.store');
        Route::put('job-postings/{jobPosting}', [JobPostingController::class, 'update'])->name('job-postings.update');
        Route::delete('job-postings/{jobPosting}', [JobPostingController::class, 'destroy'])->name('job-postings.destroy');
        Route::post('performance-reviews', [PerformanceReviewController::class, 'store'])->name('performance-reviews.store');
        Route::put('performance-reviews/{performanceReview}', [PerformanceReviewController::class, 'update'])->name('performance-reviews.update');
        Route::get('performance-reviews/{performanceReview}', [PerformanceReviewController::class, 'show'])->name('performance-reviews.show');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});

require __DIR__.'/settings.php';
