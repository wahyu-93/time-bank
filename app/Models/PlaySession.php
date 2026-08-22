<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaySession extends Model
{
    protected $fillable = [
        'child_id',
        'privilege_request_id',
        'started_at',
        'ended_at',
        'planned_minutes',
        'actual_minutes',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function privilegeRequest(): BelongsTo
    {
        return $this->belongsTo(PrivilegeRequest::class);
    }
}