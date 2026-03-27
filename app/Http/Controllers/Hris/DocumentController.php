<?php

namespace App\Http\Controllers\Hris;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Models\Applicant;
use App\Models\Document;
use App\Models\Employee;
use App\Services\Documents\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(
        public DocumentService $documentService,
    ) {}

    public function store(DocumentUploadRequest $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        if ($request->filled('employee_id')) {
            $employee = Employee::query()->findOrFail($request->integer('employee_id'));
            $this->documentService->storeForEmployee($employee, $request->file('file'), $request->string('category')->toString(), $request->user());
        }

        if ($request->filled('applicant_id')) {
            $applicant = Applicant::query()->findOrFail($request->integer('applicant_id'));
            $this->documentService->storeForApplicant($applicant, $request->file('file'), $request->string('category')->toString(), $request->user());
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
