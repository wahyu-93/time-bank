<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityClaim;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityApprovalController extends Controller
{
    public function approve(ActivityClaim $claim, Request $request, ActivityService $activityService): JsonResponse 
    {
        try {
            $claim = $activityService->approve(
                $claim,
                $request->user()
            );

            return response()->json([
                'data' => [
                    'id' => $claim->id,
                    'status' => 'completed',
                    'reward_minutes' => $claim->reward_minutes,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(ActivityClaim $claim, Request $request, ActivityService $activityService): JsonResponse 
    {
        try {
            $claim = $activityService->reject(
                $claim,
                $request->user(),
                $request->input('note')
            );

            return response()->json([
                'data' => [
                    'id' => $claim->id,
                    'status' => 'rejected',
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function penalty(ActivityClaim $claim, Request $request, ActivityService $activityService): JsonResponse
    {
        try {
            $claim = $activityService->confirmPenalty(
                $claim,
                $request->user(),
                $request->input('note')
            );

            return response()->json([
                'data' => [
                    'id' => $claim->id,
                    'status' => 'rejected',
                    'penalty_minutes' => $claim->penalty_minutes,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}