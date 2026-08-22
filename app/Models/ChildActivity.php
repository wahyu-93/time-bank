<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChildActivity extends Pivot
{
    protected $table = 'child_activities';

    protected $casts = [
        'is_active' => 'boolean',
    ];
}