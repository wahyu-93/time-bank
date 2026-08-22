<?php

namespace App\Services;

use App\Models\PlaySession;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlaySessionService
{
    public function start(PlaySession $session): PlaySession
    {
        return DB::transaction(function () use ($session) {
            if ($session->status !== 'pending') {
                throw new RuntimeException('Session cannot be started.');
            }

            $session->update([
                'status' => 'active',
                'started_at' => now(),
            ]);

            return $session->fresh();
        });
    }

    public function finish(PlaySession $session): PlaySession
    {
        return DB::transaction(function () use ($session) {
            if ($session->status !== 'active') {
                throw new RuntimeException('Session is not active.');
            }

            $startedAt = $session->started_at;
            $actualMinutes = max(
                0,
                $startedAt->diffInMinutes(now())
            );

            $actualMinutes = min(
                $actualMinutes,
                $session->planned_minutes
            );

            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'actual_minutes' => $actualMinutes,
            ]);

            return $session->fresh();
        });
    }
}