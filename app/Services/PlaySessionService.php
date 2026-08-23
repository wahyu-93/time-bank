<?php

namespace App\Services;

use App\Models\PlaySession;
use Illuminate\Support\Facades\DB;
use App\Models\Child;
use RuntimeException;

class PlaySessionService
{
   public function start(PlaySession $session): PlaySession
    {
        return DB::transaction(function () use ($session) {
            if ($session->status !== 'pending') {
                throw new RuntimeException('Session cannot be started.');
            }

            $usedToday = $this->usedToday($session->child);
            $remainingDaily = max(
                0,
                $session->child->daily_limit_minutes - $usedToday
            );

            if ($remainingDaily < $session->planned_minutes) {
                throw new RuntimeException(
                    "Daily limit exceeded. Remaining today: {$remainingDaily} minutes."
                );
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
            $elapsedSeconds = $startedAt->diffInSeconds(now());

            if ($elapsedSeconds < ($session->planned_minutes * 60)) {
                throw new RuntimeException('Session belum selesai.');
            }

            $actualMinutes = min(
                intdiv($elapsedSeconds, 60),
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

    public function usedToday(Child $child): int
    {
        $sessions = $child->playSessions()
            ->whereDate('started_at', today())
            ->whereIn('status', ['active', 'completed'])
            ->get();

        return $sessions->sum(function ($session) {
            if ($session->status === 'completed') {
                return $session->actual_minutes ?? 0;
            }

            return min(
                $session->started_at->diffInMinutes(now()),
                $session->planned_minutes
            );
        });
    }
}