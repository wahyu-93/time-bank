<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfidCard;
use App\Services\TimeBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function identify(Request $request, TimeBankService $timeBank): JsonResponse 
    {
        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:128'],
        ]);

        $card = RfidCard::query()
            ->where('uid', $validated['uid'])
            ->where('is_active', true)
            ->with('child')
            ->first();

        if (!$card || !$card->child->is_active) {
            return response()->json([
                'message' => 'RFID card is not registered.',
            ], 404);
        }

        $card->update([
            'last_used_at' => now(),
        ]);

        $balance = $timeBank->balance($card->child);

        return response()->json([
            'data' => [
                'id' => $card->child->id,
                'name' => $card->child->name,
                'age' => $card->child->birth_date?->age,
                'balance_minutes' => max($balance, 0),
                'debt_minutes' => max(-$balance, 0),
            ],
        ]);
    }
}