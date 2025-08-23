<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;

class ProfileService
{
    
    /**
     * Update the user's profile with the provided data.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $payload = Arr::only($data, [
            'password', 'dob', 'contact_number', 'confirmation_flag',
        ]);
        
        $user->fill($payload)->save();
        return $user->refresh();
    }

}