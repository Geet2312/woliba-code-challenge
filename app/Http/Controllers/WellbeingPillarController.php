<?php

namespace App\Http\Controllers;

use App\Actions\SetUserWellbeingPillarAction;
use App\Http\Requests\StoreWellbeingPillarsRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WellbeingPillarResource;
use App\Services\WellbeingPillarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class WellbeingPillarController extends Controller
{

    public function __construct(private readonly WellbeingPillarService $wellbeingPillarService)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $interests = $this->wellbeingPillarService->getPillars(orderBy: 'id');

            return response()->json([
                'data' => WellbeingPillarResource::collection($interests)
            ]);

        } catch (Throwable $e) {
            Log::error('List Wellbeing Pillar failed', [
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
    public function store(StoreWellbeingPillarsRequest $request, SetUserWellbeingPillarAction $action): JsonResponse
    {
        $user = $request->user();
        $ids = $request->validated('pillars');

        try {
            $updatedUser = $action->execute($user, $ids);

            return response()->json([
                'message' => 'Wellbeing Pillars updated successfully.',
                'data' => [
                    'wellbeing_pillars' => WellbeingPillarResource::collection($updatedUser->wellbeingPillars),
                    'user' => UserResource::make($updatedUser)
                ]
            ], 200);

        } catch (Throwable $e) {
            Log::error('Failed to save Wellbeing Pillars.', [
                    'error' => $e->getMessage()]
            );

            return response()->json([
                'message' => 'Something went wrong.'
            ], 500);
        }
    }


}
