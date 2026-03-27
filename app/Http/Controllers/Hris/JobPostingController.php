<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Models\JobPosting;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\RedirectResponse;

class JobPostingController extends Controller
{
    public function __construct(
        public RecruitmentService $recruitmentService,
    ) {}

    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        $this->authorize('create', JobPosting::class);

        $this->recruitmentService->createJobPosting($request->validated(), $request->user());

        return back()->with('success', 'Job posting created successfully.');
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->authorize('update', $jobPosting);

        $jobPosting->update($request->validated());

        return back()->with('success', 'Job posting updated successfully.');
    }

    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        $this->authorize('delete', $jobPosting);

        $jobPosting->delete();

        return back()->with('success', 'Job posting deleted successfully.');
    }
}
