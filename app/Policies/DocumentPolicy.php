<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Employee;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasEmployeeProfile() || $user->hasHrisPermission('documents.manage');
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->hasHrisPermission('documents.manage')) {
            return true;
        }

        return $document->documentable_type === Employee::class
            && $document->documentable_id === $user->employee?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasEmployeeProfile() || $user->hasHrisPermission('documents.manage');
    }

    public function update(User $user, Document $document): bool
    {
        return $user->hasHrisPermission('documents.manage');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasHrisPermission('documents.manage');
    }
}
