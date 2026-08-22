<?php

namespace App\Services;

use App\Models\Activity;
use Carbon\Carbon;

class ActivityScheduleService
{
    public function isScheduledToday(Activity $activity, ?Carbon $date = null): bool 
    {
        $date ??= today();

        $schedules = $activity->schedules()
            ->where('is_active', true)
            ->get();

        // Kalau belum ada schedule, untuk sementara
        // anggap aktivitas berlaku setiap hari.
        if ($schedules->isEmpty()) {
            return true;
        }

        foreach ($schedules as $schedule) {
            if ($this->matchesSchedule($schedule, $date)) {
                return true;
            }
        }

        return false;
    }

    private function matchesSchedule($schedule, Carbon $date): bool 
    {
        if ($schedule->starts_at && $date->lt($schedule->starts_at)) {
            return false;
        }

        if ($schedule->ends_at && $date->gt($schedule->ends_at)) {
            return false;
        }

        return match ($schedule->frequency) {
            'daily' => true,
            'weekly' => $this->matchesWeeklySchedule(
                $schedule,
                $date
            ),

            'once' => $schedule->starts_at?->isSameDay($date),
            default => false,
        };
    }

    private function matchesWeeklySchedule($schedule, Carbon $date): bool 
    {
        if (!$schedule->days_of_week) {
            return false;
        }

        return in_array(
            $date->dayOfWeekIso,
            $schedule->days_of_week,
            true
        );
    }
}