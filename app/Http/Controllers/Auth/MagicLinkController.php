<?php

namespace App\Http\Controllers\Auth;

use App\Actions\VerifyMagicLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\MagicLinkUserResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MagicLinkController extends Controller
{

    /**
     * Verify the magic link token and return user info if valid.
     * @param Request $request
     * @param VerifyMagicLinkAction $action
     * @return JsonResponse
     */
    public function show(Request $request, VerifyMagicLinkAction $action): JsonResponse
    {

        try {
            $token = $request->query('token', '');

            $result = $action->execute($token);

            if ($result === null) {
                // Invalid / expired / already used
                return response()->json([
                    'message' => 'This link is invalid or has expired.',
                ], 422);
            }

            return response()->json([
                'user' => new MagicLinkUserResponse($result['user']),
                'token' => $result['token'],
            ]);

        } catch (Throwable $e) {
            Log::error('Magic link verification failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
}
