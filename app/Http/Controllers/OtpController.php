<?php

namespace App\Http\Controllers;

use App\Http\Requests\SentOtpRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\InvitationResource;
use App\Services\EmailOtpService;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OtpController extends Controller
{
    public function __construct(
        private readonly InvitationService $service,
        private readonly EmailOtpService   $emailOtpService,
    )
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

    /**
     * @param SentOtpRequest $request
     * @return JsonResponse
     */
    public function sendEmailOtp(SentOtpRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        try {
            $this->emailOtpService->generateOtp($email);

            return response()->json([
                'message' => 'If the email is valid, an OTP has been sent.',
            ], 202);

        } catch (Throwable $e) {
            Log::error('Send OTP failed', ['email' => $email, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);

        }

    }
}

