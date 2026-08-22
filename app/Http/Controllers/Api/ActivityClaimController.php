<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityClaim;
use App\Models\Child;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function pending(Request $request): JsonResponse
    {
        $claims = ActivityClaim::with(['child', 'activity'])
            ->whereHas('child', function ($query) use ($request) {
                $query->whereIn(
                    'family_id',
                    $request->user()->families()->pluck('families.id')
                );
            })
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json([
            'data' => $claims->map(fn ($claim) => [
                'id' => $claim->id,
                'child_id' => $claim->child_id,
                'child_name' => $claim->child->name,
                'activity_id' => $claim->activity_id,
                'activity_name' => $claim->activity->name,
                'reward_minutes' => $claim->reward_minutes,
                'claimed_at' => $claim->claimed_at,
            ]),
        ]);
    }
}