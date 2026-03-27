<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class InvitationService
{
    public function sendResetLink(User $user): void
    {
        Password::sendResetLink([
            'email' => $user->email,
        ]);
    }
}
