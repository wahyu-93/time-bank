<?php

namespace App\Http\Controllers\Api;

use App\Models\Child;
use App\Http\Controllers\Controller;
use App\Services\ActivityStatusService;
use App\Services\PlaySessionService;
use App\Services\TimeBankService;
use Illuminate\Http\JsonResponse;

class ChildDashboardController extends Controller
{
    public function show(Child $child, TimeBankService $timeBank, ActivityStatusService $activityStatus, PlaySessionService $playSessions): JsonResponse 
    {
        $child->load(['activities','privileges',]);

        $balance = $timeBank->balance($child);

        $privilegeRequests = $child->privilegeRequests()
            ->whereDate('created_at', today())
            ->latest('id')
            ->get()
            ->groupBy('privilege_id')
            ->map(fn ($requests) => $requests->first());

        return response()->json([
            'data' => [
                'child' => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'age' => $child->birth_date?->age,
                    'avatar' => $child->avatar,
                ],

                'time_bank' => [
                    'balance_minutes' => max($balance, 0),
                    'debt_minutes' => max(-$balance, 0),
                ],

                'screen_time' => [
                    'used_minutes' => $playSessions->usedToday($child),
                    'daily_limit_minutes' => $child->daily_limit_minutes,
                    'remaining_minutes' => max(
                        0,
                        $child->daily_limit_minutes - $playSessions->usedToday($child)
                    ),
                ],

                'activities' => $child->activities
                    ->where('is_active', true)
                    ->values()
                    ->map(function ($activity) use (
                        $child,
                        $activityStatus
                    ) {
                        return [
                            'id' => $activity->id,
                            'name' => $activity->name,
                            'description' => $activity->description,
                            'icon' => $activity->icon,
                            'type' => $activity->type,

                            'reward_minutes' =>
                                $activity->pivot->custom_reward_minutes
                                ?? $activity->reward_minutes,

                            'penalty_minutes' =>
                                $activity->pivot->custom_penalty_minutes
                                ?? $activity->penalty_minutes,

                            'status' => $activityStatus->status(
                                $child,
                                $activity
                            ),
                        ];
                    }),

                'privileges' => $child->privileges->map(function ($privilege) use ($privilegeRequests) {
                    $request = $privilegeRequests->get($privilege->id);

                    return [
                        'id' => $privilege->id,
                        'name' => $privilege->name,
                        'description' => $privilege->description,
                        'icon' => $privilege->icon,
                        'cost_minutes' => $privilege->cost_minutes,
                        'status' => $request?->status ?? 'available',
                    ];
                }),
            ],
        ]);
    }
}