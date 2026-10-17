<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('foundation:check-database', function () {
    if (! app()->environment(['local', 'testing']) || config('database.default') !== 'mysql') {
        $this->error('Use a local/testing environment and the dedicated MySQL test connection.');

        return 1;
    }
    if (config('database.connections.mysql.url')
        || config('database.connections.mysql.database') !== 'micro_projects_test'
        || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost', '::1'], true)
        || config('database.connections.mysql.unix_socket')) {
        $this->error('Use loopback MySQL with micro_projects_test and no URL or socket override.');

        return 1;
    }
    DB::select('SELECT 1 AS connected');
    $this->info('Dedicated MySQL test connection succeeded; no schema or data was changed.');

    return 0;
})->purpose('Check the isolated MySQL test connection without running migrations');
