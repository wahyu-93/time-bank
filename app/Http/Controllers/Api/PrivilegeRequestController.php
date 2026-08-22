<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Privilege;
use App\Models\PrivilegeRequest;
use App\Models\Child;
use App\Models\PlaySession;
use App\Services\PrivilegeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrivilegeRequestController extends Controller
{
    public function store(Child $child, Privilege $privilege, PrivilegeService $service): JsonResponse
    {
        try {
            $request = $service->request($child, $privilege);

            return response()->json([
                'data' => [
                    'id' => $request->id,
                    'privilege_id' => $request->privilege_id,
                    'cost_minutes' => $request->cost_minutes,
                    'status' => $request->status,
                    'requested_at' => $request->requested_at,
                ],
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(PrivilegeRequest $privilegeRequest, Request $request, PrivilegeService $service): JsonResponse
    {
        try {
            $result = $service->approve($privilegeRequest, $request->user());

            return response()->json([
                'data' => [
                    'id' => $result->id,
                    'status' => $result->status,
                    'cost_minutes' => $result->cost_minutes,
                    'play_session_id' => $result->playSession?->id,
                ],
            ]);

            PlaySession::create([
                'child_id' => $request->child_id,
                'privilege_request_id' => $request->id,
                'duration_minutes' => $request->cost_minutes,
                'status' => 'pending',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(PrivilegeRequest $privilegeRequest, Request $request, PrivilegeService $service): JsonResponse
    {
        try {
            $result = $service->reject(
                $privilegeRequest,
                $request->user(),
                $request->input('note')
            );

            return response()->json([
                'data' => [
                    'id' => $result->id,
                    'status' => $result->status,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pending(Request $request): JsonResponse
    {
        $familyIds = $request->user()->families()->pluck('families.id');

        $requests = PrivilegeRequest::with(['child', 'privilege'])
            ->where('status', 'pending')
            ->whereHas('child', fn ($q) => $q->whereIn('family_id', $familyIds))
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests->map(fn ($item) => [
                'id' => $item->id,
                'child_id' => $item->child_id,
                'child_name' => $item->child->name,
                'privilege_id' => $item->privilege_id,
                'privilege_name' => $item->privilege->name,
                'cost_minutes' => $item->cost_minutes,
                'requested_at' => $item->requested_at,
            ]),
        ]);
    }
}