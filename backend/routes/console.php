<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// Schedule the command to fetch external data daily at 03:00 in Moscow
Schedule::command('app:fetch-external-data-daily')->dailyAt('03:00');

// Schedule the command to fetch external Home data daily  
Schedule::command('app:fetch-external-home-data-daily')->dailyAt('03:00');


// Schedule the command to fetch Trendding Home data daily  
Schedule::command('app:fetch-trending-page')->dailyAt('03:00');

// Schedule the command to fetch Trendding Home data daily  
Schedule::command('app:top100-torrents-page')->dailyAt('03:00');
// Schedule the command to fetch Movie Libary data daily  
Schedule::command('fetch:movie-library')->hourly();