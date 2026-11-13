<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Revert CS number to available if not released within 2 days
Schedule::command('csnumber:revert')->dailyAt('00:30');

Schedule::command('inventory:reset-tags')
                ->dailyAt('00:00')
                ->timezone('Asia/Manila');

Schedule::command('app:backup-database')
                ->twiceDaily(12, 21)
                ->timezone('Asia/Manila');