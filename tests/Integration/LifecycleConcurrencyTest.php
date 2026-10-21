<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Applications\ApplicationService;
use App\Services\Lifecycle\LifecycleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Support\ApplicationFixture;
use Tests\Support\FullApplicationFixture;
use Tests\TestCase;

/** Opt-in: two real PHP processes race the same form version against the approved MySQL schema. */
final class LifecycleConcurrencyTest extends TestCase
{
    public function test_two_processes_submitting_one_version_produce_exactly_one_business_event(): void
    {
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Requires the isolated MySQL test server.');
        }
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
        config([
            'database.default' => 'mysql', 'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '33072',
            'database.connections.mysql.database' => 'micro_projects_test',
            'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '',
            'database.connections.mysql.unix_socket' => '',
        ]);
        DB::purge('mysql');
        self::assertTrue(app()->environment('testing'));
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'), 'name');
        self::assertSame([], array_values(array_diff($existing, ['migrations'])), 'Refusing a pre-existing application schema.');
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        try {
            // Committed fixture: the workers are separate connections and must see it.
            $account = ApplicationFixture::account();
            $fixture = ApplicationFixture::intake(1);
            $applications = app(ApplicationService::class);
            $lifecycle = app(LifecycleService::class);
            $id = $applications->create($account, 1, ['c_MP_Name' => 'سباق', 'c_MP_DateOfRequest' => '2026-10-21'], $fixture['person'], $fixture['references']);
            FullApplicationFixture::complete($id, $fixture['person'], $fixture);

            $results = $this->race('complete', $account->id, $id, 0, $applications->find($account, $id)['version']);
            self::assertSame([200, 409], $this->statuses($results), json_encode($results));
            self::assertSame(2, DB::table('op_microproject_state')->count());
            self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
            self::assertSame(2, DB::table('op_task_microproject')->count());

            $results = $this->race('transition', $account->id, $id, $fixture['states']['Accepted'], $lifecycle->panel($account, $id)['version']);
            self::assertSame([200, 409], $this->statuses($results), json_encode($results));
            self::assertSame(3, DB::table('op_microproject_state')->count());
            self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
            self::assertSame(4, DB::table('op_task_microproject')->count());
        } finally {
            // Committed fixture rows are retained evidence to the rollback guards, so the disposable database is emptied instead.
            self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach (DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()') as $table) {
                DB::statement('DROP TABLE `'.str_replace('`', '', $table->name).'`');
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        self::assertSame([], DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'));
    }

    private function statuses(array $results): array
    {
        $codes = array_column($results, 'status');
        sort($codes);

        return $codes;
    }

    /** Both workers reach the call while this transaction owns the project row lock, then race. */
    private function race(string $mode, int $account, int $project, int $state, string $version): array
    {
        $workers = [];
        DB::beginTransaction();
        DB::table('op_microproject')->where('id', $project)->lockForUpdate()->first();
        try {
            for ($i = 0; $i < 2; $i++) {
                $worker = new Process([PHP_BINARY, __DIR__.'/fixtures/lifecycle-race.php'], base_path(), [
                    'RUN_MYSQL_SCHEMA_TESTS' => '1', 'APP_ENV' => 'testing',
                    'MYSQL_TEST_HOST' => config('database.connections.mysql.host'),
                    'MYSQL_TEST_PORT' => (string) config('database.connections.mysql.port'),
                    'MYSQL_TEST_USERNAME' => config('database.connections.mysql.username'),
                    'MYSQL_TEST_PASSWORD' => config('database.connections.mysql.password'),
                    'RACE_MODE' => $mode, 'RACE_ACCOUNT' => (string) $account, 'RACE_PROJECT' => (string) $project,
                    'RACE_STATE' => (string) $state, 'RACE_VERSION' => $version,
                ]);
                $worker->setTimeout(60);
                $worker->start();
                $workers[] = $worker;
            }
            $deadline = microtime(true) + 20;
            foreach ($workers as $worker) {
                while (! str_contains($worker->getOutput(), "ready\n")) {
                    self::assertTrue($worker->isRunning(), $worker->getErrorOutput());
                    self::assertLessThan($deadline, microtime(true), 'Worker did not reach the race barrier.');
                    usleep(10000);
                }
            }
            DB::commit();
            $results = [];
            foreach ($workers as $worker) {
                self::assertSame(0, $worker->wait(), $worker->getErrorOutput().$worker->getOutput());
                $lines = explode("\n", trim($worker->getOutput()));
                $results[] = json_decode(end($lines), true, 512, JSON_THROW_ON_ERROR);
            }

            return $results;
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
        }
    }
}
