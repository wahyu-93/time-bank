<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityClaim;
use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ActivityService
{
    public function claim(Child $child, Activity $activity): ActivityClaim
    {
        $scheduleService = app(ActivityScheduleService::class);

        if (!$scheduleService->isScheduledToday($activity)) {
            throw new RuntimeException(
                'This activity is not scheduled for today.'
            );
        }

        $childActivity = $child->activities()
            ->whereKey($activity->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$childActivity) {
            throw new RuntimeException(
                'This activity is not assigned to this child.'
            );
        }

        $claim = $child->activityClaims()
            ->where('activity_id', $activity->id)
            ->whereDate('scheduled_date', today())
            ->first();

        if (!$claim) {
            throw new RuntimeException(
                'Activity claim for today has not been created.'
            );
        }

        if ($claim->status !== 'pending') {
            throw new RuntimeException(
                'This activity has already been processed today.'
            );
        }

        $claim->update([
            'claimed_at' => now(),
            'status' => $activity->requires_approval
                ? 'pending'
                : 'approved',
        ]);

        if (!$activity->requires_approval) {
            $bank = app(TimeBankService::class);

            $bank->add(
                child: $child,
                minutes: $claim->reward_minutes,
                type: 'reward',
                description: $activity->name,
                source: $claim,
            );
        }

        return $claim->fresh();
    }

    public function approve(ActivityClaim $claim, User $parent): ActivityClaim
    {
        return DB::transaction(function () use ($claim, $parent) {
            if ($claim->status !== 'pending') {
                throw new RuntimeException(
                    'This claim has already been processed.'
                );
            }

            if (!$claim->claimed_at) {
                throw new RuntimeException(
                    'This activity has not been completed by the child.'
                );
            }

            $bank = app(TimeBankService::class);

            $bank->add(
                child: $claim->child,
                minutes: $claim->reward_minutes,
                type: 'reward',
                description: $claim->activity->name,
                user: $parent,
                source: $claim,
            );

            $claim->update([
                'status' => 'approved',
                'reviewed_by' => $parent->id,
                'reviewed_at' => now(),
            ]);

            return $claim->fresh();
        });
    }

    public function reject(
        ActivityClaim $claim,
        User $parent,
        ?string $note = null
    ): ActivityClaim {
        if ($claim->status !== 'pending') {
            throw new RuntimeException(
                'This claim has already been processed.'
            );
        }

        if (!$claim->claimed_at) {
            throw new RuntimeException(
                'This activity has not been completed by the child.'
            );
        }

        $claim->update([
            'status' => 'rejected',
            'reviewed_by' => $parent->id,
            'reviewed_at' => now(),
            'note' => $note,
        ]);

        return $claim->fresh();
    }

    public function confirmPenalty(
        ActivityClaim $claim,
        User $parent,
        ?string $note = null
    ): ActivityClaim {
        return DB::transaction(function () use ($claim, $parent, $note) {
            if ($claim->status !== 'expired') {
                throw new RuntimeException(
                    'This claim is not waiting for a penalty.'
                );
            }

            $bank = app(TimeBankService::class);

            $bank->subtract(
                child: $claim->child,
                minutes: $claim->penalty_minutes,
                type: 'penalty',
                description: $claim->activity->name,
                user: $parent,
                source: $claim,
            );

            $claim->update([
                'status' => 'rejected',
                'reviewed_by' => $parent->id,
                'reviewed_at' => now(),
                'note' => $note,
            ]);

            return $claim->fresh();
        });
    }
}