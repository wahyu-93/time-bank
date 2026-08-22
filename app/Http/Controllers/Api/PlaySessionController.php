<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaySession;
use App\Services\PlaySessionService;
use Illuminate\Http\JsonResponse;

class PlaySessionController extends Controller
{
    public function start(PlaySession $session, PlaySessionService $service): JsonResponse 
    {
        try {
            $session = $service->start($session);

            return response()->json([
                'data' => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'started_at' => $session->started_at,
                    'planned_minutes' => $session->planned_minutes,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function finish(PlaySession $session, PlaySessionService $service): JsonResponse 
    {
        try {
            $session = $service->finish($session);

            return response()->json([
                'data' => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'actual_minutes' => $session->actual_minutes,
                    'ended_at' => $session->ended_at,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(PlaySession $session): JsonResponse
    {
        $remainingSeconds = 0;

        if ($session->status === 'pending') {
            $remainingSeconds = $session->planned_minutes * 60;
        } elseif ($session->status === 'active') {
            $elapsed = $session->started_at->diffInSeconds(now());
            $remainingSeconds = max(0, ($session->planned_minutes * 60) - $elapsed);
        }

        return response()->json([
            'data' => [
                'id' => $session->id,
                'status' => $session->status,
                'planned_minutes' => $session->planned_minutes,
                'remaining_seconds' => $remainingSeconds,
                'started_at' => $session->started_at,
                'ended_at' => $session->ended_at,
            ],
        ]);
    }
}