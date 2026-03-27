<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantRequest;
use App\Http\Requests\UpdateApplicantRequest;
use App\Models\Applicant;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\RedirectResponse;

class ApplicantController extends Controller
{
    public function __construct(
        public RecruitmentService $recruitmentService,
    ) {}

    public function store(StoreApplicantRequest $request): RedirectResponse
    {
        $this->authorize('create', Applicant::class);

        $validated = $request->validated();
        $resumePath = $request->hasFile('resume')
            ? $request->file('resume')->store('applicants/resumes', 'local')
            : null;

        $applicant = Applicant::query()->create([
            'job_posting_id' => $validated['job_posting_id'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'] ?? null,
            'stage' => $validated['stage'] ?? 'applied',
            'resume_path' => $resumePath,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['scheduled_at'])) {
            $this->recruitmentService->scheduleInterview($applicant, $validated);
        }

        return back()->with('success', 'Applicant saved successfully.');
    }

    public function update(UpdateApplicantRequest $request, Applicant $applicant): RedirectResponse
    {
        $this->authorize('update', $applicant);

        $validated = $request->validated();
        $applicant->update([
            'job_posting_id' => $validated['job_posting_id'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'] ?? null,
            'stage' => $validated['stage'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['scheduled_at'])) {
            $this->recruitmentService->scheduleInterview($applicant, $validated);
        }

        return back()->with('success', 'Applicant updated successfully.');
    }

    public function destroy(Applicant $applicant): RedirectResponse
    {
        $this->authorize('delete', $applicant);

        $applicant->delete();

        return back()->with('success', 'Applicant deleted successfully.');
    }
}
