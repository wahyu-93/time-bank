<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySchedule extends Model
{
    protected $fillable = [
        'activity_id',
        'frequency',
        'days_of_week',
        'start_time',
        'due_time',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}