<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Privilege extends Model
{
    protected $fillable = [
        'family_id',
        'name',
        'description',
        'icon',
        'cost_minutes',
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

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Child::class)
            ->withPivot([
                'custom_cost_minutes',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(PrivilegeRequest::class);
    }
}