<?php

namespace App\Services\Documents;

use App\Models\Applicant;
use App\Models\Document;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentService
{
    public function storeForEmployee(Employee $employee, UploadedFile $file, string $category, User $uploader): Document
    {
        return $this->store(
            $employee,
            $file,
            "employees/{$employee->id}",
            $category,
            $uploader,
        );
    }

    public function storeForApplicant(Applicant $applicant, UploadedFile $file, string $category, User $uploader): Document
    {
        return $this->store(
            $applicant,
            $file,
            "applicants/{$applicant->id}",
            $category,
            $uploader,
        );
    }

    private function store(Employee|Applicant $owner, UploadedFile $file, string $directory, string $category, User $uploader): Document
    {
        $path = $file->storeAs(
            $directory,
            Str::uuid()->toString().'-'.$file->getClientOriginalName(),
            'local',
        );

        /** @var Document $document */
        $document = $owner->documents()->create([
            'category' => $category,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'uploaded_by' => $uploader->id,
        ]);

        return $document->fresh();
    }
}
