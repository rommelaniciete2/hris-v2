<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantHireRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Applicant;
use App\Services\Recruitment\RecruitmentService;

class ApplicantHireController extends Controller
{
    public function __invoke(
        ApplicantHireRequest $request,
        Applicant $applicant,
        RecruitmentService $recruitmentService,
    ): EmployeeResource {
        $updatedApplicant = $recruitmentService->hireApplicant($applicant, $request->validated());

        return new EmployeeResource($updatedApplicant->hiredEmployee);
    }
}
