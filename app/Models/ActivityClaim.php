<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityClaim extends Model
{
    protected $fillable = [
        'child_id',
        'activity_id',
        'scheduled_date',
        'reward_minutes',
        'penalty_minutes',
        'claimed_at',
        'status',
        'reviewed_by',
        'reviewed_at',
        'note',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'claimed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}