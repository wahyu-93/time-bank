<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'description',
        'icon',
        'type',
        'reward_minutes',
        'penalty_minutes',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            Child::class,
            'child_activities'
        )
            ->using(ChildActivity::class)
            ->withPivot([
                'custom_reward_minutes',
                'custom_penalty_minutes',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ActivityClaim::class);
    }
}