<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Child;

class ActivityStatusService
{
    public function status( Child $child, Activity $activity): string 
    {
        $claim = $child->activityClaims()
            ->where('activity_id', $activity->id)
            ->whereDate('scheduled_date', today())
            ->first();

        if (!$claim) {
            return 'pending';
        }

        return match ($claim->status) {
            'approved' => 'completed',
            'pending' => 'pending_approval',
            'rejected' => 'pending',
            'expired' => 'missed',
            default => 'pending',
        };
    }

    public function claim(Child $child, Activity $activity)
    {
        return app(ActivityService::class)
            ->claim($child, $activity);
    }
}