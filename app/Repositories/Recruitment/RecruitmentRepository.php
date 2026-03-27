<?php

namespace App\Repositories\Recruitment;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\JobPosting;
use Illuminate\Support\Collection;

class RecruitmentRepository
{
    /**
     * @return Collection<int, JobPosting>
     */
    public function jobPostings(): Collection
    {
        return JobPosting::query()->with(['department', 'position'])->latest()->limit(10)->get();
    }

    /**
     * @return Collection<int, Applicant>
     */
    public function applicants(): Collection
    {
        return Applicant::query()->with(['jobPosting', 'interviews'])->latest()->limit(10)->get();
    }

    /**
     * @return Collection<int, Interview>
     */
    public function upcomingInterviews(): Collection
    {
        return Interview::query()->with(['applicant', 'interviewer'])->orderBy('scheduled_at')->limit(10)->get();
    }
}
