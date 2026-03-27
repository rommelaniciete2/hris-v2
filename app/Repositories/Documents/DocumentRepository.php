<?php

namespace App\Repositories\Documents;

use App\Models\Document;
use App\Models\Employee;
use Illuminate\Support\Collection;

class DocumentRepository
{
    /**
     * @return Collection<int, Document>
     */
    public function forEmployee(Employee $employee): Collection
    {
        return Document::query()
            ->whereMorphedTo('documentable', $employee)
            ->latest()
            ->get();
    }
}
