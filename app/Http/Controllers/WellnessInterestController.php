<?php

namespace App\Http\Controllers;

use App\Actions\SetUserWellnessInterestAction;
use App\Http\Requests\StoreWellnessInterestsRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WellnessInterestResource;
use App\Services\WellnessInterestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class WellnessInterestController extends Controller
{
    public function __construct(
        private readonly WellnessInterestService $wellnessInterestService
    )
    {

    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $interests = $this->wellnessInterestService->getInterests();

            return response()->json([
                'data' => WellnessInterestResource::collection($interests)
            ]);

        } catch (Throwable $e) {
            Log::error('List Wellness Interest failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWellnessInterestsRequest $request, SetUserWellnessInterestAction $action): JsonResponse
    {
        $user = $request->user();
        $ids = $request->validated('interests');

        try {
            $updatedUser = $action->execute($user, $ids);

            return response()->json([
                'message' => 'Wellness Interests updated successfully.',
                'data' => [
                    'wellness_interests' => WellnessInterestResource::collection($updatedUser->wellnessInterests),
                    'user' => UserResource::make($updatedUser),
                ]

            ], 200);

        } catch (Throwable $e) {
            Log::error('Failed to save Wellness Interest.', [
                    'error' => $e->getMessage()]
            );

            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

}
