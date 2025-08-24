<?php

namespace App\Http\Controllers;

use App\Actions\VerifyEmailOtpAction;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyEmailOtpRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\UserResource;
use App\Services\EmailOtpService;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailOtpController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
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

            $invitation = $this->invitationService->findByEmail($email);

            if ($invitation === null) {
                return response()->json([
                    'data' => [
                        'message' => 'No invitation found for the provided email.',
                    ]
                ], 404);
            }

            return response()->json([
                'data' => [
                    'user' => InvitationResource::make($invitation),
                ],
            ]);

        } catch (Throwable $e) {
            Log::error('Email verification failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    /**
     * @param SendOtpRequest $request
     * @return JsonResponse
     */
    public function sendEmailOtp(SendOtpRequest $request): JsonResponse
    {
        $email = $request->validated('email');

        try {
            $invitation = $this->invitationService->findByEmail($email);

            // generate otp only if invitee email is valid
            if ($invitation !== null) {
                $this->emailOtpService->generateOtp($email);
            }

            return response()->json([
                'data' => [
                    'message' => 'If the email is valid, an OTP has been sent.',
                ]
            ], 202);

        } catch (Throwable $e) {
            Log::error('Send OTP failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);

        }

    }

    /**
     * @param VerifyEmailOtpRequest $request
     * @param VerifyEmailOtpAction $action
     * @return JsonResponse
     */
    public function verifyEmailOtp(VerifyEmailOtpRequest $request, VerifyEmailOtpAction $action): JsonResponse
    {
        try {
            $data = $request->validated();

            $result = $action->execute($data['email'], $data['otp']);

            if ($result === null) {
                return response()->json([
                    'message' => 'Invalid or expired OTP.',
                ], 422);
            }

            return response()->json([
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ]);


        } catch (Throwable $e) {
            Log::error('Email OTP verification failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);
        }

    }
}

