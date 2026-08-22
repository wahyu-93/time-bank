<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Privilege;
use App\Models\PrivilegeRequest;
use App\Models\Child;
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
}