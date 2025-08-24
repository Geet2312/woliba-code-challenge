<?php

namespace App\Services;

use App\Models\User;
use App\Models\WellnessInterest;
use Illuminate\Database\Eloquent\Collection;

class WellnessInterestService
{

    /**
     * @param array $fields
     * @param string $orderBy
     * @return Collection
     */
    public function getInterests(array $fields = ['id', 'name'], string $orderBy = 'name'): Collection
    {
        return WellnessInterest::query()
            ->orderBy($orderBy)
            ->get($fields);
    }

    /**
     * @param User $user
     * @param array $interestIds
     * @return User
     */
    public function setUserInterests(User $user, array $interestIds): User
    {
        $user->wellnessInterests()->sync($interestIds);
        
        return $user->load('wellnessInterests');
    }

}