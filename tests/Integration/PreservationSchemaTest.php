<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\ProjectNumberAllocator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/** Opt-in destructive lifecycle checks, restricted to a dedicated local test database. */
final class PreservationSchemaTest extends TestCase
{
    private int $nextId = 1000;

    private array $manifest;

    private array $snapshots = [];

    private array $runs = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for the isolated MySQL lifecycle test.');
        }

        self::assertTrue($this->app->environment('testing'));
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1'], 'Only loopback test servers are permitted.');
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
        $this->manifest = json_decode(file_get_contents(database_path('schema/preservation-v1.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_the_full_preservation_schema_lifecycle_on_isolated_mysql(): void
    {
        // Never use migrate:fresh or RefreshDatabase: reject a populated database before any DDL.
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertSame([], array_values(array_diff(array_column($tables, 'name'), ['migrations'])), 'Use an empty dedicated database; this test refuses to drop pre-existing tables.');
        if ($tables !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }

        $this->artisanSucceeds('migrate', ['--force' => true]);
        $this->assertCatalogAndPhysicalSchema();
        DB::beginTransaction();
        try {
            $this->assertRollbackRefusesMetadataWithoutRecords();
            $this->assertSyntheticRecordBoundaries();
            $this->assertRelationshipBoundaries();
            $this->assertRollbackRefusesRetainedEvidence();
        } finally {
            DB::rollBack();
        }
        try {
            $this->assertConcurrentSequenceAllocation();
        } finally {
            // These two rows are created only by this test after the empty-DB guard.
            DB::table('project_number_sequences')->whereIn('source_id', [1, 2])->delete();
        }

        $this->artisanSucceeds('migrate:rollback', ['--force' => true]);
        self::assertSame(1, (int) DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->n);
        self::assertSame(0, DB::table('migrations')->count());
        $this->artisanSucceeds('migrate', ['--force' => true]);
        $this->assertCatalogAndPhysicalSchema();
        self::assertSame(0, DB::table('source_records')->count());
        $this->artisanSucceeds('migrate:rollback', ['--force' => true]);
        self::assertSame(0, DB::table('migrations')->count());
    }

    private function assertCatalogAndPhysicalSchema(): void
    {
        self::assertEqualsCanonicalizing(['Aleppo', 'Damascus', 'Homs', 'Latakia', 'AlWadi'], DB::table('cities')->pluck('name_en')->all());
        self::assertSame(5, DB::table('sources')->count());
        self::assertSame(127, DB::table('source_tables')->count());
        self::assertEqualsCanonicalizing(array_values(array_unique(array_column($this->manifest['tables'], 'source_table'))), DB::table('source_tables')->pluck('name')->all());
        $actualTables = collect(DB::select('SELECT TABLE_NAME AS name, ENGINE AS engine, TABLE_COLLATION AS collation FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'))->keyBy('name');
        $legacyTables = $actualTables->filter(fn ($table) => str_starts_with($table->name, 'legacy_'));
        self::assertCount(616, $legacyTables);
        $operational = json_decode(file_get_contents(database_path('schema/operational-v1.json')), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(635 + count($operational['tables']) + count($operational['support_tables']), $actualTables,
            'Preservation/account foundation plus approved shared domain/support tables.');
        $columns = collect(DB::select('SELECT TABLE_NAME AS tbl, COLUMN_NAME AS col, COLUMN_TYPE AS type, IS_NULLABLE AS nullable, COLLATION_NAME AS collation, EXTRA AS extra FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()'))->groupBy('tbl');
        $indexes = collect(DB::select('SELECT TABLE_NAME AS tbl, INDEX_NAME AS idx, COLUMN_NAME AS col, NON_UNIQUE AS non_unique, SUB_PART AS prefix FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX'))->groupBy('tbl');
        $sourceColumnCount = 0;
        foreach ($this->manifest['tables'] as $table) {
            $name = $table['target_table'];
            self::assertTrue($actualTables->has($name), $name);
            self::assertSame('InnoDB', $actualTables[$name]->engine, $name);
            if (preg_match('/DEFAULT CHARSET=(\w+)(?: COLLATE=(\w+))?/', $table['original_ddl'], $charset)) {
                $collation = $charset[2] ?? ($charset[1] === 'utf8mb3' || $charset[1] === 'utf8' ? 'utf8mb3_general_ci' : null);
                if ($collation !== null) {
                    self::assertSame($collation, $actualTables[$name]->collation, $name);
                }
            }
            $actualColumns = $columns[$name]->keyBy('col');
            self::assertCount(count($table['columns']) + 3, $actualColumns, $name);
            foreach ($table['columns'] as $column) {
                $key = $column['target_column'];
                self::assertTrue($actualColumns->has($key), $name.'.'.$key);
                $actual = $actualColumns[$key];
                preg_match('/^([a-z]+(?:\([^)]*\))?(?: unsigned)?)/i', $column['target_definition'], $type);
                // MySQL may omit deprecated integer display widths; widths are not storage types.
                $normalize = fn (string $value) => preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/i', '$1', $value);
                self::assertSame($normalize($type[1]), $normalize($actual->type), $name.'.'.$key.' type');
                self::assertSame(str_contains($column['target_definition'], 'NOT NULL') ? 'NO' : 'YES', $actual->nullable, $name.'.'.$key.' nullability');
                self::assertStringNotContainsString('auto_increment', $actual->extra, $name.'.'.$key);
                if (preg_match('/COLLATE (\w+)/', $column['target_definition'], $match)) {
                    self::assertSame($match[1], $actual->collation, $name.'.'.$key.' collation');
                }
                $sourceColumnCount++;
            }
            $actualIndexes = $indexes[$name]->groupBy('idx');
            self::assertSame(['record_id'], $actualIndexes['PRIMARY']->pluck('col')->all(), $name.' technical PK');
            foreach ($table['target_indexes'] as $index) {
                preg_match('/^(UNIQUE )?KEY `([^`]+)`\s*\((.*)\)/', $index, $match);
                self::assertNotEmpty($match, $index);
                self::assertTrue($actualIndexes->has($match[2]), $name.' index '.$match[2]);
                preg_match_all('/`([^`]+)`(?:\((\d+)\))?/', $match[3], $parts, PREG_SET_ORDER);
                self::assertSame(array_column($parts, 1), $actualIndexes[$match[2]]->pluck('col')->all(), $name.' index order');
                self::assertSame($match[1] === 'UNIQUE ' ? 0 : 1, (int) $actualIndexes[$match[2]][0]->non_unique, $name.' index uniqueness');
            }
        }
        self::assertSame(3947, $sourceColumnCount);
    }

    private function assertSyntheticRecordBoundaries(): void
    {
        foreach ([1, 2] as $source) {
            $this->createSnapshot($source);
        }
        $records = [];
        foreach ([1 => 'legacy_a_microproject', 2 => 'legacy_d_microproject'] as $source => $table) {
            $record = $this->registry($source, 'microproject', 'primary:1042');
            $records[] = $record;
            // Deliberately explicit invented fields, not a source row or importer fixture.
            DB::table($table)->insert($this->typedIdentity($source, 'microproject', $record) + [
                'MP_ID' => 1042, 'MP_Name' => 'Synthetic project', 'MP_RequestedAmount' => 12.5,
                'MP_Speciality' => '', 'MP_Profit_Description' => '', 'MP_IncomeKind' => '',
                'MP_IncomeKind_Note' => '', 'MP_PlaceAddress' => '', 'MP_PlaceNote' => '',
                'MP_Note' => '', 'MP_AddressAfterFund' => '', 'ApplicationState' => 'synthetic',
            ]);
            self::assertSame(1042, (int) DB::table($table)->value('MP_ID'));
        }
        self::assertNotSame($records[0], $records[1]);
        $duplicate = $this->registry(1, 'microproject', 'primary:duplicate-attempt');
        $original = (array) DB::table('legacy_a_microproject')->first();
        $this->rejects(fn () => DB::table('legacy_a_microproject')->insert(array_replace($original, ['record_id' => $duplicate])), 1062);

        $foreignSource = $this->registry(2, 'agquestion', 'primary:7');
        $this->rejects(fn () => DB::table('legacy_a_agquestion')->insert($this->typedIdentity(2, 'agquestion', $foreignSource) + ['ID' => 7, 'Name' => 'Synthetic']), 3819);
        $this->rejects(fn () => DB::table('legacy_a_agquestion')->insert($this->typedIdentity(1, 'agquestion', $foreignSource) + ['ID' => 7, 'Name' => 'Synthetic']), 1452);
        $wrongTable = $this->registry(1, 'aganswer', 'primary:wrong-table');
        $this->rejects(fn () => DB::table('legacy_a_agquestion')->insert($this->typedIdentity(1, 'aganswer', $wrongTable) + ['ID' => 8, 'Name' => 'Synthetic']), 3819);

        foreach ([1, 2] as $ordinal) {
            $record = $this->registry(2, 'work_exp', 'ordinal:'.$ordinal, 'ordinal');
            DB::table('legacy_d_work_exp')->insert($this->typedIdentity(2, 'work_exp', $record) + ['PMP_ID' => 1042, 'P_WorkYears' => 3]);
        }
        self::assertSame(2, DB::table('legacy_d_work_exp')->where('PMP_ID', 1042)->where('P_WorkYears', 3)->count());
        self::assertSame(2, DB::table('legacy_d_work_exp')->distinct()->count('record_id'));
        $this->rejects(fn () => DB::table('snapshots')->insert(['id' => ++$this->nextId, 'source_id' => 1, 'archive_sha256' => str_repeat('a', 32), 'sql_sha256' => str_repeat('1', 32), 'manifest_locator' => 'synthetic']), 1062);
        $badSnapshot = ['id' => ++$this->nextId, 'source_id' => 2, 'table_id' => $this->tableId('agquestion'), 'snapshot_id' => $this->snapshots[1], 'source_key' => 'wrong-snapshot', 'key_sha256' => hash('sha256', 'wrong-snapshot', true), 'key_kind' => 'primary', 'import_run_id' => $this->runs[2], 'resolution_status' => 'unresolved'];
        $this->rejects(fn () => DB::table('source_records')->insert($badSnapshot), 1452);
    }

    private function assertRelationshipBoundaries(): void
    {
        $parent = $this->registry(1, 'agquestion', 'primary:parent');
        DB::table('legacy_a_agquestion')->insert($this->typedIdentity(1, 'agquestion', $parent) + ['ID' => 42, 'Name' => 'Synthetic parent']);
        $child = $this->registry(1, 'aganswer', 'primary:child');
        // An orphan raw value must remain loadable; source destructive FKs are not copied.
        DB::table('legacy_a_aganswer')->insert($this->typedIdentity(1, 'aganswer', $child) + ['ID' => 43, 'Name' => 'Synthetic child', 'Question_ID' => 999999]);
        $contract = ++$this->nextId;
        DB::table('relationship_contracts')->insert(['id' => $contract, 'source_id' => 1, 'child_table_id' => $this->tableId('aganswer'), 'parent_table_id' => $this->tableId('agquestion'), 'name' => 'synthetic-contract', 'child_columns_json' => '["Question_ID"]', 'parent_columns_json' => '["ID"]', 'evidence_locator' => 'synthetic', 'contract_kind' => 'declared']);
        $relation = ['id' => ++$this->nextId, 'contract_id' => $contract, 'source_id' => 1, 'child_table_id' => $this->tableId('aganswer'), 'child_record_id' => $child, 'parent_table_id' => $this->tableId('agquestion'), 'parent_record_id' => null, 'status' => 'unresolved'];
        $this->rejects(fn () => DB::table('record_relationships')->insert(array_replace($relation, ['status' => 'resolved'])), 3819);
        $this->rejects(fn () => DB::table('record_relationships')->insert(array_replace($relation, ['parent_record_id' => $parent])), 3819);
        $otherSourceParent = $this->registry(2, 'agquestion', 'primary:other-parent');
        $this->rejects(fn () => DB::table('record_relationships')->insert(array_replace($relation, ['parent_record_id' => $otherSourceParent, 'status' => 'resolved'])), 1452);
        DB::table('record_relationships')->insert($relation);
        DB::table('record_relationships')->where('id', $relation['id'])->update(['parent_record_id' => $parent, 'status' => 'resolved']);
        self::assertSame($parent, (int) DB::table('record_relationships')->where('id', $relation['id'])->value('parent_record_id'));
        self::assertSame(999999, (int) DB::table('legacy_a_aganswer')->value('Question_ID'));
        $this->rejects(fn () => DB::table('source_records')->where('id', $parent)->delete(), 1451);
    }

    private function createSnapshot(int $source): void
    {
        $snapshot = ++$this->nextId;
        DB::table('snapshots')->insert(['id' => $snapshot, 'source_id' => $source, 'archive_sha256' => str_repeat('a', 32), 'sql_sha256' => str_repeat((string) $source, 32), 'manifest_locator' => 'synthetic']);
        $run = ++$this->nextId;
        DB::table('migration_runs')->insert(['id' => $run, 'snapshot_id' => $snapshot, 'tool_version' => 'synthetic-test', 'started_at' => '2026-10-17 00:00:00', 'status' => 'running']);
        $this->snapshots[$source] = $snapshot;
        $this->runs[$source] = $run;
    }

    private function assertRollbackRefusesRetainedEvidence(): void
    {
        $migration = require database_path('migrations/2026_10_17_000003_create_typed_preservation_tables.php');
        try {
            $migration->down();
            self::fail('Rollback must refuse retained evidence before dropping any typed table.');
        } catch (RuntimeException $exception) {
            self::assertNotEmpty($exception->getMessage());
        }
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertCount(616, array_filter($tables, fn ($table) => str_starts_with($table->name, 'legacy_')));
        self::assertSame(1, DB::table('legacy_a_microproject')->where('MP_ID', 1042)->count());
        self::assertSame(1, DB::table('legacy_d_microproject')->where('MP_ID', 1042)->count());
    }

    private function assertRollbackRefusesMetadataWithoutRecords(): void
    {
        $id = 999;
        DB::table('snapshots')->insert([
            'id' => $id, 'source_id' => 1, 'archive_sha256' => str_repeat('m', 32),
            'sql_sha256' => str_repeat('n', 32), 'manifest_locator' => 'synthetic-metadata-only',
        ]);
        self::assertSame(0, DB::table('source_records')->count());
        foreach (['000001_create_preservation_support_tables', '000002_register_preservation_catalogs', '000003_create_typed_preservation_tables'] as $suffix) {
            $migration = require database_path('migrations/2026_10_17_'.$suffix.'.php');
            try {
                $migration->down();
                self::fail('Snapshot evidence alone must block rollback before any DROP or catalog deletion.');
            } catch (RuntimeException $exception) {
                self::assertStringContainsString('snapshots', $exception->getMessage());
            }
            self::assertSame(1, DB::table('snapshots')->where('id', $id)->count());
            self::assertSame(127, DB::table('source_tables')->count());
            self::assertSame(616, (int) DB::selectOne("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'legacy_%'")->n);
        }
        DB::table('snapshots')->where('id', $id)->delete();
    }

    private function assertConcurrentSequenceAllocation(): void
    {
        DB::table('project_number_sequences')->insert([
            ['source_id' => 1, 'next_number' => 1042],
            ['source_id' => 2, 'next_number' => 1042],
        ]);
        $workers = [];
        // Both processes reach the allocator while this transaction owns the row lock.
        DB::beginTransaction();
        DB::table('project_number_sequences')->where('source_id', 1)->lockForUpdate()->first();
        try {
            for ($i = 0; $i < 2; $i++) {
                $worker = new Process([PHP_BINARY, __DIR__.'/fixtures/allocate-project-numbers.php'], base_path(), [
                    'RUN_MYSQL_SCHEMA_TESTS' => '1',
                    'APP_ENV' => 'testing',
                    'MYSQL_TEST_HOST' => config('database.connections.mysql.host'),
                    'MYSQL_TEST_PORT' => (string) config('database.connections.mysql.port'),
                    'MYSQL_TEST_USERNAME' => config('database.connections.mysql.username'),
                    'MYSQL_TEST_PASSWORD' => config('database.connections.mysql.password'),
                ]);
                $worker->setTimeout(30);
                $worker->start();
                $workers[] = $worker;
            }
            $deadline = microtime(true) + 15;
            foreach ($workers as $worker) {
                while (! str_contains($worker->getOutput(), "ready\n")) {
                    self::assertTrue($worker->isRunning(), $worker->getErrorOutput());
                    self::assertLessThan($deadline, microtime(true), 'Worker did not reach the allocation barrier.');
                    usleep(10000);
                }
            }
            DB::commit();
            $allocated = [];
            foreach ($workers as $worker) {
                self::assertSame(0, $worker->wait(), $worker->getErrorOutput());
                $lines = explode("\n", trim($worker->getOutput()));
                $allocated = array_merge($allocated, json_decode(end($lines), true, 512, JSON_THROW_ON_ERROR));
            }
            sort($allocated, SORT_NUMERIC);
            self::assertSame(array_map('strval', range(1042, 1061)), $allocated);
            self::assertSame('1062', (string) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
            self::assertSame('1042', (string) DB::table('project_number_sequences')->where('source_id', 2)->value('next_number'));
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
        $allocator = app(ProjectNumberAllocator::class);
        DB::table('project_number_sequences')->where('source_id', 1)->update(['next_number' => '2147483647']);
        self::assertSame('2147483647', $allocator->allocate(1));
        self::assertSame('2147483648', (string) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        foreach ([1, 3] as $source) {
            try {
                $allocator->allocate($source);
                self::fail('An exhausted or uninitialized sequence must fail closed.');
            } catch (RuntimeException $exception) {
                self::assertNotEmpty($exception->getMessage());
            }
        }
        self::assertSame('2147483648', (string) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
    }

    private function registry(int $source, string $table, string $key, string $kind = 'primary'): int
    {
        $id = ++$this->nextId;
        DB::table('source_records')->insert(['id' => $id, 'source_id' => $source, 'table_id' => $this->tableId($table), 'snapshot_id' => $this->snapshots[$source], 'source_key' => $key, 'key_sha256' => hash('sha256', $key, true), 'key_kind' => $kind, 'import_run_id' => $this->runs[$source], 'resolution_status' => 'unresolved']);

        return $id;
    }

    private function typedIdentity(int $source, string $table, int $record): array
    {
        return ['record_id' => $record, 'origin_source_id' => $source, 'origin_table_id' => $this->tableId($table)];
    }

    private function tableId(string $table): int
    {
        return (int) DB::table('source_tables')->where('name', $table)->value('id');
    }

    private function rejects(callable $operation, int $mysqlCode): void
    {
        try {
            $operation();
        } catch (QueryException $exception) {
            self::assertSame($mysqlCode, (int) $exception->errorInfo[1], $exception->getMessage());

            return;
        }
        self::fail('MySQL accepted a row that should violate constraint '.$mysqlCode);
    }

    private function artisanSucceeds(string $command, array $arguments): void
    {
        $code = Artisan::call($command, $arguments);
        self::assertSame(0, $code, Artisan::output());
    }
}
