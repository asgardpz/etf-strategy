<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('etf:fetch')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();