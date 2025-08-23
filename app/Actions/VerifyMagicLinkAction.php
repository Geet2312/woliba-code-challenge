<?php

namespace App\Actions;

use App\Services\InvitationService;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyMagicLinkAction
{
    public function __construct(
        private readonly InvitationService $service,
        private readonly UserService       $userService
    )
    {
    }

    /**
     * Validates token via DB (middleware handles signed URL).
     * Burns token and returns (created or existing) user.
     *
     * @param string $token
     * @return array|null
     * @throws ValidationException
     */
    public function execute(string $token): ?array
    {
        $invitation = $this->service->findValidByToken($token);
        
        if ($invitation === null) {
            Log::warning("Invalid or expired token used: {$token}");

            return null;
        }

        $this->service->markUsed($invitation);

        $user = $this->userService->firstOrCreateFromInvitation($invitation);
        $jwt = JWTAuth::fromUser($user);
        
        return ['user' => $user, 'token' => $jwt];
    }

}