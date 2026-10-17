<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\SyntheticFoundationFixture;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class SyntheticFixtureIsolationTest extends TestCase
{
    #[DataProvider('unsafeConnections')]
    public function test_fixture_refuses_unsafe_resolved_connection_before_any_query(array $overrides): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => array_replace(config('database.connections.mysql'), [
                'driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'micro_projects_test',
                'url' => null, 'unix_socket' => '',
            ], $overrides),
        ]);
        DB::purge('mysql');

        // Reading connection configuration must reject these cases without opening a PDO connection.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('dedicated loopback');
        app(SyntheticFoundationFixture::class)->create();
    }

    public static function unsafeConnections(): array
    {
        return [
            'remote server with an allowed database name' => [['host' => '192.0.2.17']],
            'unrelated database on loopback' => [['database' => 'production_database']],
            'connection URL overrides loopback fields' => [['url' => 'mysql://example.invalid/production_database']],
            'socket bypasses loopback host' => [['unix_socket' => '/tmp/untrusted-mysql.sock']],
            'other database driver' => [['driver' => 'sqlite', 'database' => ':memory:']],
        ];
    }

    public function test_fixture_refuses_production_before_any_query(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('dedicated loopback');
        app(SyntheticFoundationFixture::class)->create();
    }
}
