<?php

declare(strict_types=1);

namespace Tests\Integration;

use Database\Schema\OperationalManifest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

require_once __DIR__.'/../../database/schema/OperationalManifest.php';

/** Opt-in lifecycle checks against an empty, loopback-only dedicated database. */
final class OperationalSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for isolated operational schema checks.');
        }
        self::assertTrue($this->app->environment('testing'));
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
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
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertStringNotContainsString('MariaDB', DB::selectOne('SELECT VERSION() AS v')->v);
    }

    public function test_approved_shared_schema_constraints_and_safe_lifecycle(): void
    {
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'), 'name');
        self::assertSame([], array_values(array_diff($existing, ['migrations'])), 'Refuse any pre-existing application tables.');
        if ($existing !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        $this->assertPhysicalSchema();
        $manifest = OperationalManifest::load();
        $project = OperationalManifest::mapping('Aleppo', 'microproject');
        $loan = OperationalManifest::mapping('Aleppo', 'loan');
        $relation = collect($manifest['relationships'])->first(fn ($r) => $r['child_source_table'] === 'loan' && $r['parent_source_table'] === 'microproject');
        self::assertNotNull($relation);

        DB::beginTransaction();
        try {
            $this->entity(900001, 1, 1, $project['source_table_id']);
            $this->entity(900002, 2, 2, $project['source_table_id']);
            $this->entity(900003, 1, 1, $loan['source_table_id']);
            DB::table('op_microproject')->insert(['id' => 900001, 'city_id' => 1, 'source_id' => 1, 'source_table_id' => $project['source_table_id'], 'c_MP_ID' => 7]);
            DB::table('op_microproject')->insert(['id' => 900002, 'city_id' => 2, 'source_id' => 2, 'source_table_id' => $project['source_table_id'], 'c_MP_ID' => 7]);
            $child = ['id' => 900003, 'city_id' => 1, 'source_id' => 1, 'source_table_id' => $loan['source_table_id']];
            $this->mustReject(fn () => DB::table('op_loan')->insert($child + [$relation['resolved_column'] => 900002, $relation['state_column'] => 'resolved']));
            $this->mustReject(fn () => DB::table('op_loan')->insert($child + [$relation['state_column'] => 'resolved']));
            $this->mustReject(fn () => DB::table('op_loan')->insert($child + [$relation['resolved_column'] => 900001, $relation['state_column'] => 'orphan']));
            $this->mustReject(fn () => $this->entity(900004, 2, 1, $project['source_table_id']));
            $this->mustReject(fn () => DB::table('op_loan')->insert(array_replace($child, ['source_table_id' => $project['source_table_id']])));
            DB::table('op_loan')->insert($child + [$relation['resolved_column'] => 900001, $relation['state_column'] => 'resolved']);
            $this->mustReject(fn () => DB::table('op_microproject')->where('id', 900001)->delete());
            $this->assertRollbackRefusesEvidence();
        } finally {
            DB::rollBack();
        }
        // Retained import metadata alone must prevent the first schema DROP too.
        DB::beginTransaction();
        try {
            DB::table('snapshots')->insert(['id' => 900001, 'source_id' => 1, 'archive_sha256' => str_repeat('a', 32), 'sql_sha256' => str_repeat('b', 32), 'manifest_locator' => 'synthetic/schema-test']);
            $this->assertRollbackRefusesEvidence();
        } finally {
            DB::rollBack();
        }
        self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        self::assertSame(0, DB::table('migrations')->count());
        self::assertSame(1, (int) DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->n);
    }

    private function assertPhysicalSchema(): void
    {
        self::assertSame(OperationalManifest::SHA256, hash_file('sha256', database_path('schema/operational-v1.json')));
        $tables = OperationalManifest::tables();
        self::assertCount(122, $tables);
        $columns = collect(DB::select('SELECT TABLE_NAME AS tbl,COLUMN_NAME AS col,COLUMN_TYPE AS type,IS_NULLABLE AS nullable,COLLATION_NAME AS collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()'))->groupBy('tbl');
        $indices = collect(DB::select('SELECT TABLE_NAME AS tbl,INDEX_NAME AS idx,COLUMN_NAME AS col,NON_UNIQUE AS non_unique FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX'))->groupBy('tbl');
        $fks = collect(DB::select('SELECT TABLE_NAME AS tbl,CONSTRAINT_NAME AS name,DELETE_RULE AS del,UPDATE_RULE AS upd FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE()'))->groupBy('tbl');
        foreach ($tables as $table) {
            $name = $table['name'];
            self::assertTrue($columns->has($name), $name);
            $actual = $columns[$name]->keyBy('col');
            self::assertCount(count($table['columns']), $actual, $name);
            foreach ($table['columns'] as $column) {
                $found = $actual[$column['name']];
                preg_match('/^([a-z]+(?:\([^)]*\))?(?: unsigned)?)/i', $column['definition'], $type);
                $normalize = fn (string $value): string => preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/i', '$1', strtolower($value));
                self::assertSame($normalize($type[1]), $normalize($found->type), $name.'.'.$column['name']);
                self::assertSame(str_contains($column['definition'], 'NOT NULL') ? 'NO' : 'YES', $found->nullable, $name.'.'.$column['name']);
                if (preg_match('/COLLATE\s+(\w+)/i', $column['definition'], $collation)) {
                    self::assertSame($collation[1], $found->collation, $name.'.'.$column['name']);
                }
            }
            $actualIndices = $indices[$name]->groupBy('idx');
            foreach ($table['indexes'] as $ordinal => $index) {
                $indexName = $index['kind'] === 'PRIMARY' ? 'PRIMARY' : OperationalManifest::objectName('ix', $name, $ordinal);
                self::assertTrue($actualIndices->has($indexName), $name.'.'.$indexName);
                self::assertSame($index['columns'], $actualIndices[$indexName]->pluck('col')->all());
                self::assertSame($index['kind'] === 'INDEX' ? 1 : 0, (int) $actualIndices[$indexName]->first()->non_unique);
            }
            self::assertCount(count($table['foreign_keys']), $fks->get($name, collect()), $name);
            foreach ($fks->get($name, collect()) as $foreign) {
                self::assertSame('RESTRICT', $foreign->del);
                self::assertSame('RESTRICT', $foreign->upd);
            }
        }
    }

    private function entity(int $id, int $city, int $source, int $kind): void
    {
        DB::table('operational_entities')->insert(['id' => $id, 'city_id' => $city, 'source_id' => $source, 'source_table_id' => $kind, 'identity_kind' => 'web', 'created_at' => '2026-10-17 00:00:00.000000']);
    }

    private function mustReject(callable $operation): void
    {
        try {
            $operation();
            self::fail('Expected an enforced MySQL constraint to reject this write.');
        } catch (QueryException) {
            self::assertTrue(true);
        }
    }

    private function assertRollbackRefusesEvidence(): void
    {
        $migration = require database_path('migrations/2026_10_17_000006_create_shared_operational_tables.php');
        try {
            $migration->down();
            self::fail('Populated evidence must reject rollback before any DDL.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('rollback', $error->getMessage());
        }
        $operationalNames = array_column(OperationalManifest::tables(), 'name');
        self::assertSame(122, count(array_intersect(array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'), 'name'), $operationalNames)));
    }
}
