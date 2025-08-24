<?php

namespace App\Actions;

use App\Models\User;
use App\Services\WellbeingPillarService;

class SetUserWellbeingPillarAction
{
    public function __construct(private readonly WellbeingPillarService $wellbeingPillarService)
    {
    }

    /**
     * @param User $user
     * @param array $pillarIds
     * @return User
     */
    public function execute(User $user, array $pillarIds): User
    {
       return $this->wellbeingPillarService->setUserPillars($user, $pillarIds);
    }

}