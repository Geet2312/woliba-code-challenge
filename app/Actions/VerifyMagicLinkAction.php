<?php

namespace App\Actions;

use App\Models\User;
use App\Services\InvitationService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     * @return JsonResponse|User
     * @throws ValidationException
     */
    public function execute(string $token): ?User
    {
        $invitation = $this->service->findValidByToken($token);
        if ($invitation === null) {
            Log::warning("Invalid or expired token used: {$token}");

            return null;
        }

        $this->service->markUsed($invitation);

        return $this->userService->firstOrCreateFromInvitation($invitation);
    }

}