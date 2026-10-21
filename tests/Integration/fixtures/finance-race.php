<?php

declare(strict_types=1);

use App\Models\WebAccount;
use App\Services\Finance\FinanceService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
$account = WebAccount::findOrFail((int) getenv('RACE_ACCOUNT'));
$project = (int) getenv('RACE_PROJECT');
$finance = $app->make(FinanceService::class);
echo "ready\n";
flush();
try {
    if (getenv('RACE_MODE') === 'loan') {
        $finance->saveLoan($account, $project, ['currency' => 'USD', 'exchange_rate' => '15000', 'amount' => '1000', 'date' => '2026-10-21', 'returned' => '1200', 'payment' => '120', 'note' => '', 'receipt' => 'RACE'], getenv('RACE_VERSION'));
    } else {
        $finance->savePayments($account, $project, (int) getenv('RACE_LOAN'), [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1']], getenv('RACE_VERSION'));
    }
    echo json_encode(['status' => 200])."\n";
} catch (HttpException $exception) {
    echo json_encode(['status' => $exception->getStatusCode()])."\n";
} catch (ValidationException $exception) {
    echo json_encode(['status' => $exception->status])."\n";
}
