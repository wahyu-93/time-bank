<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityClaim;
use Illuminate\Support\Facades\DB;

class ActivityExpiryService
{
    public function createDailyClaims(): int
    {
        $created = 0;
        $scheduleService = app(ActivityScheduleService::class);

        Activity::query()
            ->where('is_active', true)
            ->with('children')
            ->get()
            ->each(function (Activity $activity) use (&$created, $scheduleService) {
                if (!$scheduleService->isScheduledToday($activity)) {
                    return;
                }

                foreach ($activity->children as $child) {
                    if (!$child->pivot->is_active) {
                        continue;
                    }

                    $exists = $child->activityClaims()
                        ->where('activity_id', $activity->id)
                        ->whereDate('scheduled_date', today())
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $reward = $child->pivot->custom_reward_minutes
                        ?? $activity->reward_minutes;

                    $penalty = $child->pivot->custom_penalty_minutes
                        ?? $activity->penalty_minutes;

                    $child->activityClaims()->create([
                        'activity_id' => $activity->id,
                        'scheduled_date' => today(),
                        'reward_minutes' => $reward,
                        'penalty_minutes' => $penalty,
                        'status' => 'pending',
                    ]);

                    $created++;
                }
            });

        return $created;
    }

    public function expirePending(): int
    {
        return DB::transaction(function () {
            return ActivityClaim::query()
                ->where('status', 'pending')
                ->whereDate('scheduled_date', '<', today())
                ->whereNull('claimed_at')
                ->update([
                    'status' => 'expired',
                ]);
        });
    }
}