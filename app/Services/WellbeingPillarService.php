<?php

namespace App\Services;

use App\Models\User;
use App\Models\WellbeingPillar;
use Illuminate\Database\Eloquent\Collection;

class WellbeingPillarService
{

    /**
     * @param array $fields
     * @param string $orderBy
     * @return Collection
     */
    public function getPillars(array $fields = ['id', 'name'], string $orderBy = 'name'): Collection
    {
        return WellbeingPillar::query()
            ->orderBy($orderBy)
            ->get($fields);
    }

    /**
     * @param User $user
     * @param array $pillarIds
     * @return User
     */
    public function setUserPillars(User $user, array $pillarIds): User
    {
        $user->wellbeingPillars()->sync($pillarIds);
        
        $user->markRegistrationComplete();

        return $user->load('wellbeingPillars');
        
    }
}