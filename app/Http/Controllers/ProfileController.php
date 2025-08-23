<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfileController extends Controller
{
    /**
     * ProfileController constructor.
     *
     * @param ProfileService $service
     */
    public function __construct(private readonly ProfileService $service)
    {
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = $request->user();
            $updatedData = $this->service->updateProfile($user, $request->validated());

            return response()->json([
                'message' => 'Profile updated successfully.',
                'data' => new UserResource($updatedData),
            ]);
            
        } catch (Throwable $e) {
            Log::error('Profile update failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
}
