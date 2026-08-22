<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Child;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;

class ActivityClaimController extends Controller
{
    public function store(Child $child, Activity $activity, ActivityService $activityService): JsonResponse 
    {
        try {
            $claim = $activityService->claim(
                $child,
                $activity
            );

            return response()->json([
                'data' => [
                    'id' => $claim->id,
                    'activity_id' => $claim->activity_id,
                    'status' => $claim->status === 'pending'
                        ? 'pending_approval'
                        : 'completed',
                    'reward_minutes' => $claim->reward_minutes,
                    'claimed_at' => $claim->claimed_at,
                ],
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}