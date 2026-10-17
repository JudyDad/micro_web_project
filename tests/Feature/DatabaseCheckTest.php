<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseCheckTest extends TestCase
{
    public function test_database_url_override_is_rejected_without_connecting(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'micro_projects_test',
            'database.connections.mysql.url' => 'mysql://user:example@localhost/other_database',
        ]);
        DB::shouldReceive('select')->never();
        $this->artisan('foundation:check-database')->assertExitCode(1);
    }

    public function test_production_check_is_rejected_without_connecting(): void
    {
        $this->app->instance('env', 'production');
        DB::shouldReceive('select')->never();
        $this->artisan('foundation:check-database')->assertExitCode(1);
    }
}
