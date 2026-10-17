<?php

declare(strict_types=1);

use App\Services\ProjectNumberAllocator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// A subprocess fixture, never an application command or a source-data tool.
if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1' || getenv('APP_ENV') !== 'testing') {
    exit(2);
}
$host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
if (! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
    exit(2);
}
$root = dirname(__DIR__, 3);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'database.default' => 'mysql',
    'database.connections.mysql.url' => null,
    'database.connections.mysql.host' => $host,
    'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '33072',
    'database.connections.mysql.database' => 'micro_projects_test',
    'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
    'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '',
    'database.connections.mysql.unix_socket' => '',
]);
DB::purge('mysql');
if (DB::selectOne('SELECT DATABASE() AS db')->db !== 'micro_projects_test') {
    exit(2);
}
echo "ready\n";
flush();
$numbers = [];
for ($i = 0; $i < 10; $i++) {
    $numbers[] = $app->make(ProjectNumberAllocator::class)->allocate(1);
}
echo json_encode($numbers, JSON_THROW_ON_ERROR)."\n";
