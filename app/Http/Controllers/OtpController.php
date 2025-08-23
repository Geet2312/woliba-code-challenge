<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\InvitationResource;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OtpController extends Controller
{
    public function __construct(private readonly InvitationService $service)
    {

    }

    /**
     * Verify email is invited
     * 
     * @param VerifyEmailRequest $request
     * @return JsonResponse
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $email = $request->validated('email');

            $invitation = $this->service->findByEmail($email);

            if ($invitation === null) {
                return response()->json([
                    'message' => 'No invitation found for the provided email.',
                ], 404);
            }

            return response()->json([
                'user' => InvitationResource::make($invitation),
            ]);

        } catch (Throwable $e) {
            Log::error('Email verification failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
}

