<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('activities:prepare')->dailyAt('00:01');
Schedule::command('activities:expire')->dailyAt('00:05');