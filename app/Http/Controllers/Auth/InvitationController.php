<?php

namespace App\Http\Controllers\Auth;

use App\Actions\InviteUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use Illuminate\Http\JsonResponse;

class InvitationController extends Controller
{
    /**
     * Handle the incoming request to invite a user.
     *
     * @param StoreInvitationRequest $request
     * @param InviteUserAction $action
     * @return JsonResponse
     */
    public function store(StoreInvitationRequest $request, InviteUserAction $action): JsonResponse
    {
        $data = $request->validated();
        $action->execute($data['first_name'], $data['last_name'], $data['email'], $data['is_magic_link'] ?? false);

        if (!$request->boolean('is_magic_link')) {
            return response()->json([
                'data' => [
                    'message' => 'If the email is valid, user has been invited without sending a magic link email.',
                ]
            ], 202);
        }

        // Response for Magic Link
        return response()->json([
            'data' => [
                'message' => 'If the email is valid, an invitation will be sent.',
            ]
        ], 202);
    }
}
