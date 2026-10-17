<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('foundation:check-database', function () {
    if (! app()->environment(['local', 'testing']) || config('database.default') !== 'mysql') {
        $this->error('Use a local/testing environment and the dedicated MySQL test connection.');

        return 1;
    }
    if (config('database.connections.mysql.url') || config('database.connections.mysql.database') !== 'micro_projects_test') {
        $this->error('This check only accepts the micro_projects_test database.');

        return 1;
    }
    DB::select('SELECT 1 AS connected');
    $this->info('Dedicated MySQL test connection succeeded; no schema or data was changed.');

    return 0;
})->purpose('Check the isolated MySQL test connection without running migrations');
