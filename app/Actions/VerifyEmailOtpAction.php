<?php

namespace App\Actions;

use App\Services\EmailOtpService;
use App\Services\InvitationService;
use App\Services\UserService;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyEmailOtpAction
{
    public function __construct(
        private readonly EmailOtpService $emailOtpService,
        private readonly InvitationService $invitationService,
        private readonly UserService $userService,
    )
    {
    }

    /**
     * @param string $email
     * @param string $otp
     * @return array|null
     */
    public function execute(string $email, string $otp): ?array
    {
        $invitation = $this->invitationService->findByEmail($email);
        if ($invitation === null) {
           return null;
        }

        $isValid = $this->emailOtpService->verifyAndBurn($email, $otp);
        if (!$isValid) {
           return null;
        }

        $user  = $this->userService->firstOrCreateFromInvitation($invitation);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

}