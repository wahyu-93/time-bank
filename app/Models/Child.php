<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'birth_date',
        'avatar',
        'daily_limit_minutes',
        'max_debt_minutes',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function rfidCards(): HasMany
    {
        return $this->hasMany(RfidCard::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class,'child_activities')
            ->using(ChildActivity::class)
            ->withPivot([
                'custom_reward_minutes',
                'custom_penalty_minutes',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function activityClaims(): HasMany
    {
        return $this->hasMany(ActivityClaim::class);
    }

    public function timeTransactions(): HasMany
    {
        return $this->hasMany(TimeTransaction::class);
    }

    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(Privilege::class)
            ->withPivot([
                'custom_cost_minutes',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function privilegeRequests(): HasMany
    {
        return $this->hasMany(PrivilegeRequest::class);
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }
}