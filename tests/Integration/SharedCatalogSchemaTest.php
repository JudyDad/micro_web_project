<?php

declare(strict_types=1);

namespace Tests\Integration;

use Database\Schema\OperationalManifest;
use Database\Schema\SharedCatalogManifest;
use Database\Schema\SharedCatalogRollbackGuard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

require_once __DIR__.'/../../database/schema/SharedCatalogManifest.php';
require_once __DIR__.'/../../database/schema/SharedCatalogRollbackGuard.php';

final class SharedCatalogSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for isolated shared catalog checks.');
        }
        self::assertTrue($this->app->environment('testing'));
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
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertStringNotContainsString('MariaDB', DB::selectOne('SELECT VERSION() AS v')->v);
    }

    public function test_shared_catalog_physical_delta_and_provenance_boundaries(): void
    {
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'), 'name');
        self::assertSame([], array_values(array_diff($existing, ['migrations'])), 'Only an empty dedicated test database is allowed.');
        if ($existing !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        $this->assertPhysicalDelta();
        $kind = OperationalManifest::mapping('Aleppo', 'category')['source_table_id'];
        $time = '2026-10-18 00:00:00.000000';
        DB::beginTransaction();
        try {
            foreach ([1, 2] as $source) {
                $id = 920000 + $source;
                DB::table('snapshots')->insert(['id' => $id, 'source_id' => $source, 'archive_sha256' => str_repeat((string) $source, 32), 'sql_sha256' => str_repeat((string) ($source + 2), 32), 'manifest_locator' => 'synthetic/shared-schema']);
                DB::table('migration_runs')->insert(['id' => $id, 'snapshot_id' => $id, 'tool_version' => 'synthetic', 'started_at' => $time, 'status' => 'running']);
                DB::table('source_records')->insert(['id' => $id, 'source_id' => $source, 'table_id' => $kind, 'snapshot_id' => $id, 'source_key' => 'synthetic-key', 'key_sha256' => str_repeat('k', 32), 'key_kind' => 'primary', 'import_run_id' => $id, 'resolution_status' => 'pending']);
                $this->entity($id, $source, $kind);
                DB::table('operational_origins')->insert(['source_record_id' => $id, 'source_id' => $source, 'source_table_id' => $kind, 'snapshot_id' => $id, 'entity_id' => $id, 'linked_by_run_id' => $id, 'linked_at' => $time]);
                DB::table('op_category')->insert(['id' => $id, 'city_id' => $source, 'source_id' => $source, 'source_table_id' => $kind, 'c_ID' => 1, 'c_Name' => 'Synthetic category']);
            }
            $group = ['id' => 920001, 'source_table_id' => $kind, 'schema_contract_sha256' => str_repeat('s', 32), 'full_table_sha256' => str_repeat('f', 32), 'row_count' => 6, 'decision_locator' => 'synthetic/decision', 'created_at' => $time];
            $this->mustReject(fn () => DB::table('shared_catalog_groups')->insert($group + ['status' => 'published']));
            DB::table('shared_catalog_groups')->insert($group);
            $member = ['group_id' => 920001, 'city_id' => 1, 'source_id' => 1, 'source_table_id' => $kind, 'snapshot_id' => 920001, 'row_count' => 6, 'verified_at' => $time, 'evidence_locator' => 'synthetic/verification'];
            $this->mustReject(fn () => DB::table('shared_catalog_memberships')->insert(array_replace($member, ['row_count' => 7])));
            $this->mustReject(fn () => DB::table('shared_catalog_memberships')->insert(array_replace($member, ['city_id' => 2, 'source_id' => 2])));
            DB::table('shared_catalog_memberships')->insert($member);
            $canonical = ['id' => 920001, 'group_id' => 920001, 'source_table_id' => $kind, 'row_ordinal' => 1, 'row_sha256' => str_repeat('r', 32), 'duplicate_ordinal' => 1, 'c_ID' => 1, 'c_Name' => 'Synthetic category'];
            $this->mustReject(fn () => DB::table('shared_category')->insert(array_replace($canonical, ['source_table_id' => $kind + 1])));
            DB::table('shared_category')->insert($canonical);
            $pointers = ['shared_group_id' => 920001, 'shared_row_id' => 920001, 'shared_snapshot_id' => 920001];
            $this->mustReject(fn () => DB::table('op_category')->where('id', 920001)->update(['shared_group_id' => 920001]));
            $this->mustReject(fn () => DB::table('op_category')->where('id', 920001)->update($pointers));
            $cleared = $pointers + ['c_ID' => null, 'c_Name' => null];
            $this->mustReject(fn () => DB::table('op_category')->where('id', 920002)->update($cleared));
            $this->entity(920003, 1, $kind);
            DB::table('op_category')->insert(['id' => 920003, 'city_id' => 1, 'source_id' => 1, 'source_table_id' => $kind]);
            $this->mustReject(fn () => DB::table('op_category')->where('id', 920003)->update($cleared));
            DB::table('op_category')->where('id', 920001)->update($cleared);
            $this->mustReject(fn () => DB::table('shared_category')->where('id', 920001)->delete());
            try {
                SharedCatalogRollbackGuard::assertEmptyEvidence([], SharedCatalogManifest::alterations());
                self::fail('Alias evidence alone must reject rollback.');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('aliases', $error->getMessage());
            }
            $migration = require database_path('migrations/2026_10_18_000007_create_shared_catalogs.php');
            try {
                $migration->down();
                self::fail('Sharing evidence must reject rollback before any DDL.');
            } catch (RuntimeException $error) {
                self::assertStringContainsString('rollback', $error->getMessage());
            }
            self::assertSame(1, DB::table('shared_catalog_groups')->count());
            self::assertTrue(DB::getSchemaBuilder()->hasColumn('op_category', 'shared_row_id'));
        } finally {
            DB::rollBack();
        }
        // No conversion occurred: dropping only the sharing delta must retain
        // ordinary unshared operational payload and its entity identity.
        $this->entity(920010, 1, $kind);
        DB::table('op_category')->insert(['id' => 920010, 'city_id' => 1, 'source_id' => 1, 'source_table_id' => $kind, 'c_ID' => 9, 'c_Name' => 'Unshared synthetic category']);
        $migration = require database_path('migrations/2026_10_18_000007_create_shared_catalogs.php');
        $migration->down();
        self::assertFalse(DB::getSchemaBuilder()->hasTable('shared_category'));
        self::assertFalse(DB::getSchemaBuilder()->hasColumn('op_category', 'shared_row_id'));
        self::assertSame('Unshared synthetic category', DB::table('op_category')->where('id', 920010)->value('c_Name'));
        // Direct down does not alter the framework migration ledger. Reinstall
        // before testing the ordinary complete reverse-order lifecycle.
        $migration->up();
        DB::table('op_category')->where('id', 920010)->delete();
        DB::table('operational_entities')->where('id', 920010)->delete();
        self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        self::assertSame(0, DB::table('migrations')->count());
        self::assertSame(1, (int) DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()')->n);
    }

    private function assertPhysicalDelta(): void
    {
        self::assertSame(SharedCatalogManifest::SHA256, hash_file('sha256', database_path('schema/shared-catalog-v1.json')));
        self::assertCount(57, SharedCatalogManifest::tables());
        self::assertCount(55, SharedCatalogManifest::alterations());
        self::assertCount(58, SharedCatalogManifest::groups());
        $columns = collect(DB::select('SELECT TABLE_NAME AS tbl,COLUMN_NAME AS col,COLUMN_TYPE AS type,IS_NULLABLE AS nullable,COLLATION_NAME AS collation FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()'))->groupBy('tbl');
        $indexes = collect(DB::select('SELECT TABLE_NAME AS tbl,INDEX_NAME AS idx,COLUMN_NAME AS col,NON_UNIQUE AS non_unique FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX'))->groupBy('tbl');
        $foreignKeys = collect(DB::select('SELECT TABLE_NAME AS tbl,CONSTRAINT_NAME AS name,DELETE_RULE AS del,UPDATE_RULE AS upd FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE()'))->groupBy('tbl');
        foreach (SharedCatalogManifest::tables() as $table) {
            $name = $table['name'];
            $actual = $columns[$name]->keyBy('col');
            self::assertCount(count($table['columns']), $actual, $name);
            $this->assertColumns($name, $table['columns'], $actual);
            $this->assertIndexes($name, $table['indexes'], $indexes[$name]->groupBy('idx'));
            self::assertCount(count($table['foreign_keys']), $foreignKeys[$name], $name);
            foreach ($foreignKeys[$name] as $foreign) {
                self::assertSame('RESTRICT', $foreign->del);
                self::assertSame('RESTRICT', $foreign->upd);
            }
        }
        foreach (SharedCatalogManifest::alterations() as $alteration) {
            $name = $alteration['table'];
            $this->assertColumns($name, $alteration['add_columns'], $columns[$name]->keyBy('col'));
            $this->assertIndexes($name, $alteration['add_indexes'], $indexes[$name]->groupBy('idx'));
            foreach ($alteration['add_foreign_keys'] as $ordinal => $foreign) {
                $found = $foreignKeys[$name]->firstWhere('name', SharedCatalogManifest::objectName('fk', $name, $ordinal));
                self::assertNotNull($found, $name);
                self::assertSame('RESTRICT', $found->del);
                self::assertSame('RESTRICT', $found->upd);
            }
        }
    }

    private function assertColumns(string $table, array $definitions, $actual): void
    {
        foreach ($definitions as $column) {
            $found = $actual[$column['name']];
            preg_match('/^([a-z]+(?:\([^)]*\))?(?: unsigned)?)/i', $column['definition'], $type);
            $normalize = fn (string $value): string => preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/i', '$1', strtolower($value));
            self::assertSame($normalize($type[1]), $normalize($found->type), $table.'.'.$column['name']);
            self::assertSame(str_contains($column['definition'], 'NOT NULL') ? 'NO' : 'YES', $found->nullable);
            if (preg_match('/COLLATE\s+(\w+)/i', $column['definition'], $collation)) {
                self::assertSame($collation[1], $found->collation);
            }
        }
    }

    private function assertIndexes(string $table, array $definitions, $actual): void
    {
        foreach ($definitions as $ordinal => $index) {
            $name = $index['kind'] === 'PRIMARY' ? 'PRIMARY' : SharedCatalogManifest::objectName('ix', $table, $ordinal);
            self::assertTrue($actual->has($name));
            self::assertSame($index['columns'], $actual[$name]->pluck('col')->all());
            self::assertSame($index['kind'] === 'INDEX' ? 1 : 0, (int) $actual[$name]->first()->non_unique);
        }
    }

    private function entity(int $id, int $source, int $kind): void
    {
        DB::table('operational_entities')->insert(['id' => $id, 'city_id' => $source, 'source_id' => $source, 'source_table_id' => $kind, 'identity_kind' => 'web', 'created_at' => '2026-10-18 00:00:00.000000']);
    }

    private function mustReject(callable $operation): void
    {
        try {
            $operation();
            self::fail('MySQL must enforce this approved sharing boundary.');
        } catch (QueryException) {
            self::assertTrue(true);
        }
    }
}
