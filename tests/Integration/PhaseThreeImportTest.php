<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Import\DeterministicImporter;
use App\Services\Import\FrozenSourceReader;
use App\Services\Import\ImportCheckpointStore;
use App\Services\Import\KeyEncoder;
use App\Services\Reconciliation\IndependentReconciler;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/** Durable commits on an initially empty, dedicated loopback MySQL database only. */
final class PhaseThreeImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for isolated import/reconciliation checks.');
        }
        self::assertTrue($this->app->environment('testing'));
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
        config(['database.default' => 'mysql', 'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $host, 'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '33072',
            'database.connections.mysql.database' => 'micro_projects_test',
            'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '', 'database.connections.mysql.unix_socket' => '']);
        DB::purge('mysql');
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertStringNotContainsString('MariaDB', DB::selectOne('SELECT VERSION() AS v')->v);
    }

    public function test_durable_import_resume_reconciliation_and_corruption_detection(): void
    {
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertSame([], array_values(array_diff(array_column($tables, 'name'), ['migrations'])), 'Refusing a populated database.');
        if ($tables !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        try {
            $aleppo = $this->reader('Aleppo', 1);
            $homs = $this->reader('Homs', 3);
            $importer = new DeterministicImporter(DB::connection());
            $reconciler = app(IndependentReconciler::class);

            // An interrupted uncommitted batch leaves no occurrence behind.
            $this->mustFail(fn () => $importer->import($aleppo, 1, static function (string $event, array $info): void {
                if ($event === 'before_commit' && $info['table_id'] === 1 && $info['phase'] === 'preservation') {
                    throw new RuntimeException('Synthetic interruption before commit.');
                }
            }), 'Synthetic interruption before commit.');
            self::assertSame(0, DB::table('source_records')->count());

            // Lost acknowledgement after a real commit must retain its single occurrence.
            $this->mustFail(fn () => $importer->import($aleppo, 1, static function (string $event, array $info): void {
                if ($event === 'after_commit' && $info['table_id'] === 1 && $info['phase'] === 'preservation') {
                    throw new RuntimeException('Synthetic lost commit acknowledgement.');
                }
            }), 'Synthetic lost commit acknowledgement.');
            self::assertSame(1, DB::table('source_records')->count());
            $runId = (int) DB::table('migration_run_contracts')->where('snapshot_id', $aleppo->contract()['snapshot_id'])->value('run_id');
            self::assertSame('interrupted', DB::table('migration_runs')->where('id', $runId)->value('status'));
            $resumeStatuses = [];
            $importer->import($aleppo, 1, static function (string $event, array $info) use (&$resumeStatuses, $runId): void {
                if ($event === 'before_batch' && $info['run_id'] === $runId) {
                    $resumeStatuses[] = DB::table('migration_runs')->where('id', $runId)->value('status');
                }
            });
            self::assertNotEmpty($resumeStatuses);
            self::assertSame(['running'], array_values(array_unique($resumeStatuses)));
            self::assertSame('imported', DB::table('migration_runs')->where('id', $runId)->value('status'));
            $this->mustFail(fn () => $importer->import($homs, 1, static function (string $event, array $info): void {
                if ($event === 'after_row' && $info['phase'] === 'relationships') {
                    throw new RuntimeException('Synthetic interruption inside relationship transaction.');
                }
            }), 'Synthetic interruption inside relationship transaction.');
            self::assertSame(0, DB::table('record_relationships')->where('source_id', 3)->count());
            $importer->import($homs, 2);
            $ids = DB::table('source_records')->orderBy('id')->pluck('id')->all();
            $entities = DB::table('operational_origins')->orderBy('source_record_id')->pluck('entity_id')->all();
            $relationships = DB::table('record_relationships')->count();
            $importer->import($aleppo, 2);
            self::assertSame($ids, DB::table('source_records')->orderBy('id')->pluck('id')->all());
            self::assertSame($entities, DB::table('operational_origins')->orderBy('source_record_id')->pluck('entity_id')->all());
            self::assertSame($relationships, DB::table('record_relationships')->count());
            self::assertSame(0, DB::table('source_publications')->count());
            self::assertSame(0, DB::table('web_accounts')->count());

            foreach ([$aleppo, $homs] as $reader) {
                $report = $reconciler->reconcile($reader);
                self::assertTrue($report['matches'], json_encode($report['mismatches']));
                self::assertTrue($report['core_ready']);
                self::assertFalse($report['publishable']);
                self::assertGreaterThan(0, $report['checked_cells']);
                self::assertGreaterThan(0, $report['checked_relationships']);
            }
            self::assertSame(2, DB::table('op_microproject')->where('c_MP_ID', 1042)->count());
            self::assertSame(2, DB::table('legacy_a_t1')->count(), 'Identical keyless rows must remain distinct.');
            self::assertSame(2, DB::table('legacy_h_t1')->count());
            self::assertSame(2, DB::table('legacy_a_microproject_ag_answer')->where('MicroProject_ID', 1042)->where('Question_ID', 1)->count(), 'All three composite key components must remain distinct.');

            // Independent evidence must detect both preserved and operational corruption.
            $name = DB::table('legacy_a_agquestion')->where('ID', 1)->value('Name');
            DB::table('legacy_a_agquestion')->where('ID', 1)->update(['Name' => 'changed']);
            self::assertFalse($reconciler->reconcile($aleppo)['matches']);
            $this->mustFail(fn () => $importer->import($aleppo, 2));
            DB::table('legacy_a_agquestion')->where('ID', 1)->update(['Name' => $name]);

            $op = DB::table('op_agquestion')->where('source_id', 1)->where('c_ID', 1)->first();
            DB::table('op_agquestion')->where('id', $op->id)->update(['c_Name' => 'changed']);
            self::assertFalse($reconciler->reconcile($aleppo)['matches']);
            DB::table('op_agquestion')->where('id', $op->id)->update(['c_Name' => $op->c_Name]);

            $entity = DB::table('operational_entities')->where('id', $op->id)->first();
            DB::table('operational_entities')->where('id', $op->id)->update(['first_key_bytes' => 'corrupt-key']);
            self::assertFalse($reconciler->reconcile($aleppo)['matches']);
            $this->mustFail(fn () => $importer->import($aleppo, 2));
            DB::table('operational_entities')->where('id', $op->id)->update(['first_key_bytes' => $entity->first_key_bytes]);

            $record = DB::table('source_records')->where('source_id', 1)->first();
            $foreignRun = DB::table('migration_runs')->where('snapshot_id', $homs->contract()['snapshot_id'])->value('id');
            DB::table('source_records')->where('id', $record->id)->update(['import_run_id' => $foreignRun]);
            self::assertFalse($reconciler->reconcile($aleppo)['matches']);
            $this->mustFail(fn () => $importer->import($aleppo, 2));
            DB::table('source_records')->where('id', $record->id)->update(['import_run_id' => $record->import_run_id]);

            $file = DB::table('op_file')->where('source_id', 1)->first();
            DB::table('op_file')->where('id', $file->id)->update(['c_File_Content' => "\x00changed"]);
            self::assertFalse($reconciler->reconcile($aleppo)['matches']);
            DB::table('op_file')->where('id', $file->id)->update(['c_File_Content' => $file->c_File_Content]);

            $this->assertRelationshipCorruptionDetected($aleppo, $reconciler);
            $this->assertLeaseFencing($aleppo);
            self::assertTrue($reconciler->reconcile($aleppo)['matches']);
        } finally {
            // Only data created after this test's empty-database guard is removed.
            $this->removeSyntheticEvidence();
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
            self::assertSame(0, DB::table('migrations')->count());
        }
    }

    private function assertRelationshipCorruptionDetected(FrozenSourceReader $reader, IndependentReconciler $reconciler): void
    {
        $schema = OperationalManifest::load();
        $contract = array_values(array_filter($schema['relationships'], fn ($item) => $item['child_source_table'] === 'aganswer' && $item['parent_source_table'] === 'agquestion'))[0];
        $child = DB::table('op_aganswer')->where('source_id', 1)->first();
        $wrongParent = DB::table('op_agquestion')->where('source_id', 1)->where('c_ID', 2)->value('id');
        $field = $contract['resolved_column'];
        DB::table('op_aganswer')->where('id', $child->id)->update([$field => $wrongParent]);
        self::assertFalse($reconciler->reconcile($reader)['matches']);
        DB::table('op_aganswer')->where('id', $child->id)->update([$field => $child->{$field}]);
    }

    private function assertLeaseFencing(FrozenSourceReader $reader): void
    {
        $checkpoint = DB::table('migration_checkpoints')->where('snapshot_id', $reader->contract()['snapshot_id'])->where('phase', 'preservation')->first();
        $store = new ImportCheckpointStore(DB::connection());
        $first = $store->acquire((int) $checkpoint->run_id, (int) $checkpoint->snapshot_id, $checkpoint->mapping_sha256, (int) $checkpoint->source_table_id, 'preservation', str_repeat('a', 16));
        $this->mustFail(fn () => $store->acquire((int) $checkpoint->run_id, (int) $checkpoint->snapshot_id, $checkpoint->mapping_sha256, (int) $checkpoint->source_table_id, 'preservation', str_repeat('b', 16)));
        DB::table('migration_checkpoints')->where('id', $checkpoint->id)->update(['lease_expires_at' => '2000-01-01 00:00:00']);
        $second = $store->acquire((int) $checkpoint->run_id, (int) $checkpoint->snapshot_id, $checkpoint->mapping_sha256, (int) $checkpoint->source_table_id, 'preservation', str_repeat('b', 16));
        $this->mustFail(fn () => DB::transaction(fn () => $store->lock($first)));
        $store->release($second);
    }

    private function reader(string $city, int $source): FrozenSourceReader
    {
        $schema = OperationalManifest::load();
        $maps = array_values(array_filter($schema['mappings'], fn ($item) => $item['city'] === $city));
        $sqlHash = hash('sha256', 'synthetic-sql-'.$city);
        $archive = hash('sha256', 'synthetic-archive-'.$city);
        $snapshot = DB::table('snapshots')->insertGetId(['source_id' => $source, 'archive_sha256' => hex2bin($archive), 'sql_sha256' => hex2bin($sqlHash), 'manifest_locator' => 'synthetic:phase-three:'.$source]);
        $rows = [];
        foreach ($maps as $map) {
            $table = $map['source_table'];
            $rows[$table] = [];
            $count = in_array($table, ['agquestion', 'aganswer', 't1', 'microproject_ag_answer'], true) ? 2 : (in_array($table, ['aid', 'file', 'microproject'], true) ? 1 : 0);
            for ($ordinal = 1; $ordinal <= $count; $ordinal++) {
                $values = [];
                foreach ($map['columns'] as $column) {
                    $definition = $column['source_definition'];
                    $values[$column['source_column']] = str_contains($definition, 'NOT NULL') ? (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|float|double|decimal)/i', $definition) ? '0' : '') : null;
                }
                foreach ($map['legacy_primary_key'] as $name) {
                    $values[$name] = (string) $ordinal;
                }
                if ($table === 'microproject') {
                    $values['MP_ID'] = '1042';
                    $values['MP_Name'] = 'Synthetic '.$city;
                    $values['MP_RequestedAmount'] = '17.25';
                }
                if ($table === 'agquestion') {
                    $values['Name'] = 'سؤال '.$ordinal.' ';
                }
                if ($table === 'aganswer') {
                    $values['Name'] = 'إجابة';
                    $values['Question_ID'] = '1';
                }
                if ($table === 'aid') {
                    $values['Name'] = 'نص '."e\u{301}".' ';
                }
                if ($table === 'file') {
                    $values['File_Name'] = 'synthetic.bin';
                    $values['File_Content'] = "\x00\xffsynthetic";
                    $values['MicroProject_ID'] = '1042';
                }
                if ($table === 't1') {
                    $values['AvgSal'] = '0.10000000149011612';
                    $values['workCount'] = '9007199254740993';
                }
                if ($table === 'microproject_ag_answer') {
                    $values['MicroProject_ID'] = '1042';
                    $values['Question_ID'] = '1';
                    $values['Answer_ID'] = (string) $ordinal;
                }
                $columns = [];
                foreach ($map['legacy_primary_key'] as $key) {
                    foreach ($map['columns'] as $column) {
                        if ($column['source_column'] === $key) {
                            $columns[$key] = $column['source_definition'];
                        }
                    }
                }
                $key = $columns === [] ? KeyEncoder::ordinal($sqlHash, $table, $ordinal) : KeyEncoder::primary($columns, $values);
                $rows[$table][] = ['ordinal' => $ordinal, 'cursor' => 'fixture:'.$table.':'.$ordinal, 'key' => $key, 'key_kind' => $columns === [] ? 'ordinal' : 'primary', 'values' => $values];
            }
        }
        $contract = ['source_id' => $source, 'city_id' => $source, 'city' => $city, 'snapshot_id' => (int) $snapshot,
            'sql_sha256' => $sqlHash, 'archive_sha256' => $archive, 'frozen_manifest_sha256' => hash('sha256', 'synthetic-manifest-'.$city),
            'mapping_sha256' => OperationalManifest::SHA256, 'schema_sha256' => OperationalManifest::SHA256,
            'extractor_sha256' => hash('sha256', 'synthetic-reader'), 'extractor_version' => 'synthetic-v1', 'key_encoding_version' => KeyEncoder::VERSION,
            'table_counts' => array_map('count', $rows)];

        return new class($contract, $rows) implements FrozenSourceReader
        {
            public function __construct(private array $metadata, private array $data) {}

            public function contract(): array
            {
                return $this->metadata;
            }

            public function tables(): array
            {
                return array_keys($this->data);
            }

            public function rows(string $table): iterable
            {
                yield from $this->data[$table];
            }
        };
    }

    private function mustFail(callable $operation, ?string $expectedMessage = null): void
    {
        try {
            $operation();
            self::fail('The invalid or interrupted operation succeeded.');
        } catch (RuntimeException $error) {
            self::assertNotSame('', $error->getMessage());
            if ($expectedMessage !== null) {
                self::assertSame($expectedMessage, $error->getMessage());
            }
        }
    }

    private function removeSyntheticEvidence(): void
    {
        $schema = OperationalManifest::load();
        DB::transaction(static function () use ($schema): void {
            DB::table('migration_exceptions')->delete();
            DB::table('record_relationships')->delete();
            foreach (array_reverse($schema['parent_first_order']) as $table) {
                DB::table($table)->delete();
            }
            DB::table('operational_origins')->delete();
            DB::table('operational_entities')->delete();
            DB::table('migration_checkpoints')->delete();
            DB::table('migration_run_contracts')->delete();
            foreach ($schema['mappings'] as $map) {
                DB::table($map['preservation_table'])->delete();
            }
            DB::table('source_records')->delete();
            DB::table('relationship_contracts')->delete();
            DB::table('migration_runs')->delete();
            DB::table('snapshots')->delete();
        });
    }
}
