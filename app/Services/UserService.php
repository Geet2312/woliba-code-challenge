<?php

namespace App\Services;
use App\Models\Invitation;
use App\Models\User;

class UserService
{
    /**
     * Create or retrieve a user based on the invitation details.
     *
     * @param Invitation $invitation
     * @return User
     */
    public function firstOrCreateFromInvitation(Invitation $invitation): User
    {
        return User::firstOrCreate(
            ['email' => $invitation->email],
            [
                'first_name' => $invitation->first_name,
                'last_name' => $invitation->last_name,
                'invitation_id' => $invitation->id,
            ]
        );
    }
    
}