<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeRequest extends Model
{
    protected $fillable = [
        'child_id',
        'privilege_id',
        'cost_minutes',
        'status',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(Privilege::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}