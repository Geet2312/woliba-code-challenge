<?php

namespace App\Actions;

use App\Models\User;
use App\Services\WellnessInterestService;

class SetUserWellnessInterestAction
{
    public function __construct(private readonly WellnessInterestService $service)
    {

    }

    /**
     * @param User $user
     * @param array $interestIds
     * @return User
     */
    public function execute(User $user, array $interestIds): User
    {
        return $this->service->setUserInterests($user, $interestIds);
    }
}