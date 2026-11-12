<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('migrate_seed', function () {
    Artisan::call('migrate:rollback');
    echo "===Migrate Rollback Done=== \n";
    Artisan::call('migrate');
    echo "===Migrated=== \n";
    Artisan::call('db:seed');
    echo "===Seed Generated=== \n";
});

Illuminate\Support\Facades\Schedule::command('listings:prune')->dailyAt('03:30');
Illuminate\Support\Facades\Schedule::command('sanctum:prune-expired --hours=24')->daily();
