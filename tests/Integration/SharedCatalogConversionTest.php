<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Import\DeterministicImporter;
use App\Services\Import\FrozenSourceReader;
use App\Services\Import\KeyEncoder;
use App\Services\Reconciliation\IndependentReconciler;
use App\Services\Sharing\CatalogSourceProof;
use App\Services\Sharing\SharedCatalogConverter;
use App\Services\Sharing\SharedPayloadProjection;
use Database\Schema\OperationalManifest;
use Database\Schema\SharedCatalogManifest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/** Synthetic proof is injected only here; the production command requires archive proof. */
final class SharedCatalogConversionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for isolated catalog conversion checks.');
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

    public function test_complete_table_conversion_is_atomic_scoped_and_independently_reconciled(): void
    {
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertSame([], array_values(array_diff(array_column($tables, 'name'), ['migrations'])), 'Refusing a populated database.');
        if ($tables !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        try {
            $readers = [];
            $importer = new DeterministicImporter(DB::connection());
            foreach ([1 => 'Aleppo', 2 => 'Damascus', 3 => 'Homs', 4 => 'Latakia', 5 => 'AlWadi'] as $source => $city) {
                $readers[$city] = $this->reader($city, $source);
                $importer->import($readers[$city], 100);
            }
            $reconciler = app(IndependentReconciler::class);
            $proof = $this->syntheticProof();
            $converter = new SharedCatalogConverter(DB::connection(), $proof, $reconciler);
            $age = SharedCatalogManifest::groups('age')[0];
            $category = SharedCatalogManifest::groups('category')[0];
            $task = SharedCatalogManifest::groups('task')[0];
            $entities = DB::table('operational_entities')->orderBy('id')->get()->all();
            $origins = DB::table('operational_origins')->orderBy('source_record_id')->get()->all();
            $assignments = DB::table('op_task_microproject')->orderBy('id')->get()->all();
            $users = DB::table('op_task_user')->orderBy('id')->get()->all();

            // Five equal rows of a six-row table are insufficient: the whole group must fail.
            $this->mustFail(fn () => $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', null, [$category]), 'Synthetic whole table differs.');
            self::assertSame(0, DB::table('shared_category')->count());
            self::assertSame(0, DB::table('shared_catalog_groups')->count());
            // A permissive test-only proof cannot bypass the converter's own full-byte comparison.
            $permissive = new SharedCatalogConverter(DB::connection(), $this->syntheticProof(false), $reconciler);
            $this->mustFail(fn () => $permissive->convert($readers, 'synthetic:decision', 'synthetic:evidence', null, [$category]), 'An entire member table differs; no subset can be shared.');
            self::assertSame(0, DB::table('shared_category')->count());
            self::assertSame(0, DB::table('shared_catalog_groups')->count());

            $changed = DB::table('op_age')->where('source_id', 3)->first();
            DB::table('op_age')->where('id', $changed->id)->update(['c_Name' => 'changed target only']);
            $this->mustFail(fn () => $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', null, [$age]), 'Independent whole-table source, origin, payload or relationship verification failed.');
            self::assertSame(0, DB::table('shared_age')->count());
            DB::table('op_age')->where('id', $changed->id)->update(['c_Name' => $changed->c_Name]);

            $this->mustFail(fn () => $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', function (string $event): void {
                if ($event === 'before_commit') {
                    $this->assertCompetingSourceWriterBlocked();
                    throw new RuntimeException('Synthetic sharing interruption before commit.');
                }
            }, [$age]), 'Synthetic sharing interruption before commit.');
            self::assertSame(0, DB::table('shared_age')->count());
            self::assertSame(0, DB::table('shared_catalog_memberships')->count());
            self::assertSame(30, DB::table('op_age')->whereNull('shared_row_id')->whereNotNull('c_ID')->count());

            $this->mustFail(fn () => $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', static function (string $event): void {
                if ($event === 'after_commit') {
                    throw new RuntimeException('Synthetic sharing acknowledgement lost.');
                }
            }, [$age]), 'Synthetic sharing acknowledgement lost.');
            self::assertSame(6, DB::table('shared_age')->count());
            self::assertSame(30, DB::table('op_age')->whereNotNull('shared_row_id')->whereNull('c_ID')->count());
            self::assertSame(5, DB::table('shared_catalog_memberships')->count());
            $rowIds = DB::table('shared_age')->orderBy('id')->pluck('id')->all();
            $again = $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', null, [$age]);
            self::assertSame(1, $again['reused_groups']);
            self::assertSame($rowIds, DB::table('shared_age')->orderBy('id')->pluck('id')->all());

            $converter->convert($readers, 'synthetic:decision', 'synthetic:evidence', null, [$task]);
            self::assertSame(39, DB::table('shared_task')->count());
            self::assertSame(195, DB::table('op_task')->whereNotNull('shared_row_id')->whereNull('c_T_ID')->count());
            self::assertEquals($entities, DB::table('operational_entities')->orderBy('id')->get()->all());
            self::assertEquals($origins, DB::table('operational_origins')->orderBy('source_record_id')->get()->all());
            self::assertEquals($assignments, DB::table('op_task_microproject')->orderBy('id')->get()->all());
            self::assertEquals($users, DB::table('op_task_user')->orderBy('id')->get()->all());
            self::assertFalse(DB::getSchemaBuilder()->hasTable('shared_task_microproject'));
            self::assertFalse(DB::getSchemaBuilder()->hasTable('shared_task_user'));
            $taskLink = array_values(array_filter(OperationalManifest::load()['relationships'], fn (array $relation): bool => $relation['child_source_table'] === 'task_microproject' && $relation['parent_source_table'] === 'task'))[0]['resolved_column'];
            $joined = DB::table('op_task_microproject as assignment')->join('op_task as alias', function ($join) use ($taskLink): void {
                $join->on('alias.id', '=', 'assignment.'.$taskLink)->on('alias.source_id', '=', 'assignment.source_id')->on('alias.city_id', '=', 'assignment.city_id');
            })->join('shared_task as canonical', 'canonical.id', '=', 'alias.shared_row_id')->get(['assignment.source_id', 'assignment.c_State', 'canonical.c_T_Name']);
            self::assertCount(5, $joined);
            foreach ($joined as $row) {
                self::assertSame('مهمة 1', $row->c_T_Name);
                self::assertSame((int) $row->source_id % 2, (int) $row->c_State);
            }

            foreach ($readers as $reader) {
                $report = $reconciler->reconcile($reader);
                self::assertTrue($report['matches'], json_encode($report['mismatches']));
                self::assertTrue($report['core_ready']);
                self::assertFalse($report['publishable']);
                $importer->import($reader, 100);
            }
            self::assertSame(0, DB::table('source_publications')->count());
            self::assertSame(0, DB::table('web_accounts')->count());
            $this->assertScopeAndCorruption($readers['Aleppo'], $reconciler, $importer);
        } finally {
            $this->removeSyntheticEvidence();
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
            self::assertSame(0, DB::table('migrations')->count());
        }
    }

    private function assertScopeAndCorruption(FrozenSourceReader $reader, IndependentReconciler $reconciler, DeterministicImporter $importer): void
    {
        $projection = new SharedPayloadProjection(DB::connection());
        $alias = DB::table('op_age')->where('source_id', 1)->first();
        $canonical = DB::table('shared_age')->where('id', $alias->shared_row_id)->first();
        $snapshot = $reader->contract()['snapshot_id'];
        $expected = $projection->project('age', $alias, 1, $snapshot);
        self::assertSame((int) $alias->id, (int) $expected->id);
        self::assertNotNull($expected->c_ID);
        $this->mustFail(fn () => $projection->project('age', $canonical, 1, $snapshot));
        $this->mustFail(fn () => $projection->project('age', $alias, 3, $snapshot));
        $tampered = clone $alias;
        $tampered->city_id = 3;
        $this->mustFail(fn () => $projection->project('age', $tampered, 1, $snapshot));
        $this->get('/en/shared/age/'.$canonical->id)->assertNotFound();
        $this->get('/en/shared_task/'.$canonical->id)->assertNotFound();

        $this->mustFail(fn () => DB::table('op_age')->where('id', $alias->id)->update(['city_id' => 3]));
        $this->mustFail(fn () => DB::table('op_age')->where('id', $alias->id)->update(['c_Name' => 'duplicate alias payload']));
        DB::table('shared_age')->where('id', $canonical->id)->update(['c_Name' => 'corrupt canonical']);
        self::assertFalse($reconciler->reconcile($reader)['matches']);
        $this->mustFail(fn () => $importer->import($reader, 100));
        DB::table('shared_age')->where('id', $canonical->id)->update(['c_Name' => $canonical->c_Name]);
        DB::table('shared_age')->where('id', $canonical->id)->update(['row_sha256' => str_repeat('x', 32)]);
        self::assertFalse($reconciler->reconcile($reader)['matches']);
        $this->mustFail(fn () => $importer->import($reader, 100));
        DB::table('shared_age')->where('id', $canonical->id)->update(['row_sha256' => $canonical->row_sha256]);
        $importer->import($reader, 100);
        self::assertTrue($reconciler->reconcile($reader)['matches']);
    }

    private function assertCompetingSourceWriterBlocked(): void
    {
        config(['database.connections.sharing_contender' => config('database.connections.mysql')]);
        DB::purge('sharing_contender');
        $other = DB::connection('sharing_contender');
        $other->statement('SET SESSION innodb_lock_wait_timeout = 1');
        $other->beginTransaction();
        try {
            try {
                $other->table('sources')->where('id', 1)->lockForUpdate()->first();
                self::fail('A competing source writer bypassed conversion locking.');
            } catch (QueryException $error) {
                self::assertSame(1205, (int) $error->errorInfo[1]);
            }
        } finally {
            $other->rollBack();
            DB::disconnect('sharing_contender');
            DB::purge('sharing_contender');
        }
    }

    private function syntheticProof(bool $enforceEquality = true): CatalogSourceProof
    {
        return new class($enforceEquality) implements CatalogSourceProof
        {
            public function __construct(private bool $enforceEquality) {}

            public function verify(array $group, array $readersByCity): array
            {
                $reference = null;
                $sources = [];
                foreach ($group['cities'] as $city) {
                    $reader = $readersByCity[$city];
                    $rows = [];
                    foreach ($reader->rows($group['table']) as $row) {
                        // A test-only equality oracle, independent of converter serialization/hashes.
                        $values = $row['values'];
                        ksort($values, SORT_STRING);
                        $rows[] = $values;
                    }
                    if (count($rows) !== $group['rows_per_city'] || ($this->enforceEquality && $reference !== null && $reference !== $rows)) {
                        throw new RuntimeException('Synthetic whole table differs.');
                    }
                    $reference = $rows;
                    $context = $reader->contract();
                    $sources[$city] = array_intersect_key($context, array_flip(['archive_sha256', 'sql_sha256', 'frozen_manifest_sha256', 'snapshot_id']));
                }

                return ['verified' => true, 'full_table_sha256' => $group['full_table_sha256'], 'schema_contract_sha256' => $group['schema_contract_sha256'], 'sources' => $sources];
            }
        };
    }

    private function reader(string $city, int $source): FrozenSourceReader
    {
        $maps = array_values(array_filter(OperationalManifest::load()['mappings'], fn ($item) => $item['city'] === $city));
        $sql = hash('sha256', 'synthetic-sharing-sql-'.$city);
        $archive = hash('sha256', 'synthetic-sharing-archive-'.$city);
        $snapshot = DB::table('snapshots')->insertGetId(['source_id' => $source, 'archive_sha256' => hex2bin($archive), 'sql_sha256' => hex2bin($sql), 'manifest_locator' => 'synthetic:sharing:'.$source]);
        $data = [];
        foreach ($maps as $map) {
            $table = $map['source_table'];
            $data[$table] = [];
            $count = match ($table) {
                'age', 'category' => 6,
                'task' => 39,
                'microproject', 'task_microproject', 'task_user' => 1,
                default => 0,
            };
            for ($ordinal = 1; $ordinal <= $count; $ordinal++) {
                $values = [];
                foreach ($map['columns'] as $column) {
                    $definition = $column['source_definition'];
                    $values[$column['source_column']] = str_contains($definition, 'NOT NULL') ? (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|float|double|decimal)/i', $definition) ? '0' : '') : null;
                }
                foreach ($map['legacy_primary_key'] as $column) {
                    $values[$column] = (string) $ordinal;
                }
                if ($table === 'age') {
                    $values['Name'] = 'Synthetic age '.$ordinal;
                    $values['Min'] = (string) ($ordinal * 10);
                    $values['Max'] = (string) ($ordinal * 10 + 9);
                    $values['isActive'] = '1';
                } elseif ($table === 'category') {
                    $values['Name'] = ($city === 'Homs' && $ordinal === 6 ? 'Different category ' : 'Synthetic category ').$ordinal;
                } elseif ($table === 'task') {
                    $values['T_Name'] = 'مهمة '.(string) $ordinal;
                    $values['T_State'] = '1';
                    $values['T_Type'] = 'S';
                } elseif ($table === 'microproject') {
                    $values['MP_ID'] = '1042';
                    $values['MP_Name'] = 'Synthetic '.$city;
                } elseif ($table === 'task_microproject') {
                    $values['MicroProject_ID'] = '1042';
                    $values['Task_ID'] = '1';
                    $values['State'] = (string) ($source % 2);
                } elseif ($table === 'task_user') {
                    $values['Task_ID'] = '1';
                }
                $keyColumns = [];
                foreach ($map['legacy_primary_key'] as $key) {
                    foreach ($map['columns'] as $column) {
                        if ($column['source_column'] === $key) {
                            $keyColumns[$key] = $column['source_definition'];
                        }
                    }
                }
                $key = KeyEncoder::primary($keyColumns, $values);
                $data[$table][] = ['ordinal' => $ordinal, 'cursor' => 'synthetic:'.$table.':'.$ordinal, 'key' => $key, 'key_kind' => 'primary', 'values' => $values];
            }
        }
        $context = ['source_id' => $source, 'city_id' => $source, 'city' => $city, 'snapshot_id' => (int) $snapshot,
            'sql_sha256' => $sql, 'archive_sha256' => $archive, 'frozen_manifest_sha256' => hash('sha256', 'synthetic-sharing-manifest-'.$city),
            'mapping_sha256' => OperationalManifest::SHA256, 'schema_sha256' => OperationalManifest::SHA256,
            'extractor_sha256' => hash('sha256', 'synthetic-sharing-reader'), 'extractor_version' => 'synthetic-sharing-v1', 'key_encoding_version' => KeyEncoder::VERSION,
            'table_counts' => array_map('count', $data)];

        return new class($context, $data) implements FrozenSourceReader
        {
            public function __construct(private array $context, private array $data) {}

            public function contract(): array
            {
                return $this->context;
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

    private function mustFail(callable $operation, ?string $message = null): void
    {
        try {
            $operation();
            self::fail('An invalid or interrupted sharing operation succeeded.');
        } catch (RuntimeException $error) {
            self::assertNotSame('', $error->getMessage());
            if ($message !== null) {
                self::assertSame($message, $error->getMessage());
            }
        }
    }

    private function removeSyntheticEvidence(): void
    {
        DB::transaction(static function (): void {
            DB::table('migration_exceptions')->delete();
            DB::table('record_relationships')->delete();
            foreach (array_reverse(OperationalManifest::load()['parent_first_order']) as $table) {
                DB::table($table)->delete();
            }
            foreach (SharedCatalogManifest::load()['canonical_tables'] as $table) {
                DB::table($table['name'])->delete();
            }
            DB::table('shared_catalog_memberships')->delete();
            DB::table('shared_catalog_groups')->delete();
            DB::table('operational_origins')->delete();
            DB::table('operational_entities')->delete();
            DB::table('migration_checkpoints')->delete();
            DB::table('migration_run_contracts')->delete();
            foreach (OperationalManifest::load()['mappings'] as $map) {
                DB::table($map['preservation_table'])->delete();
            }
            DB::table('source_records')->delete();
            DB::table('relationship_contracts')->delete();
            DB::table('migration_runs')->delete();
            DB::table('snapshots')->delete();
        });
    }
}
