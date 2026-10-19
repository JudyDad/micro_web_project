<?php

declare(strict_types=1);

namespace App\Services\Import;

use Database\Schema\OperationalManifest;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;
use Throwable;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

final class DeterministicImporter
{
    private readonly ImportCheckpointStore $checkpoints;

    private readonly ImportRecordWriter $writer;

    private readonly ImportRelationshipResolver $relationships;

    public function __construct(private readonly ConnectionInterface $db)
    {
        $this->checkpoints = new ImportCheckpointStore($db);
        $this->writer = new ImportRecordWriter($db);
        $this->relationships = new ImportRelationshipResolver($db);
    }

    public function import(FrozenSourceReader $reader, int $batchSize = 250, ?callable $hook = null): array
    {
        if ($batchSize < 1 || $batchSize > 1000 || $this->db->getDriverName() !== 'mysql') {
            throw new RuntimeException('Import requires MySQL and a batch size from 1 to 1000.');
        }
        $config = $this->db->getConfig();
        if (! app()->environment(['local', 'testing'])
            || ! in_array($config['database'] ?? null, ['micro_projects_local', 'micro_projects_test'], true)
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Import requires a dedicated loopback local/testing target.');
        }
        $context = $this->preflight($reader);
        $context['run_id'] = $this->run($context);
        $owner = random_bytes(16);
        $sqlMode = (string) $this->db->selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
        // Retain strict range/truncation rejection; only allow legacy zero dates.
        $compatibility = implode(',', array_diff(explode(',', $sqlMode), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));
        $this->db->statement('SET SESSION sql_mode = ?', [$compatibility]);
        try {
            foreach (['preservation', 'relationships'] as $phase) {
                foreach ($reader->tables() as $table) {
                    $mapping = OperationalManifest::mapping($context['city'], $table);
                    $contracts = array_values(array_filter(OperationalManifest::load()['relationships'],
                        fn ($r) => $r['child_source_table'] === $table
                            && count(array_filter($r['occurrences'], fn ($o) => $o['city'] === $context['city'])) > 0));
                    $lease = $this->checkpoints->acquire($context['run_id'], $context['snapshot_id'], hex2bin($context['mapping_sha256']),
                        $mapping['source_table_id'], $phase, $owner);
                    try {
                        $this->table($reader, $context, $mapping, $contracts, $phase, $batchSize, $lease, $hook);
                    } finally {
                        $this->checkpoints->release($lease);
                    }
                }
            }
            // This is staged completion, never reconciliation or publication.
            $this->db->table('migration_runs')->where('id', $context['run_id'])->update(['status' => 'imported', 'completed_at' => now('UTC')]);

            return ['run_id' => $context['run_id'],
                'preserved' => $this->db->table('source_records')->where('snapshot_id', $context['snapshot_id'])->count(),
                'operational' => $this->db->table('operational_origins')->where('snapshot_id', $context['snapshot_id'])->count(),
                'relationships' => $this->db->table('record_relationships as rr')->join('source_records as r', 'r.id', '=', 'rr.child_record_id')->where('r.snapshot_id', $context['snapshot_id'])->count(),
                'exceptions' => $this->db->table('migration_exceptions')->where('run_id', $context['run_id'])->count()];
        } catch (Throwable $error) {
            $this->db->table('migration_runs')->where('id', $context['run_id'])->update(['status' => 'interrupted']);
            throw $error;
        } finally {
            $this->db->statement('SET SESSION sql_mode = ?', [$sqlMode]);
        }
    }

    private function table(FrozenSourceReader $reader, array $context, array $mapping, array $contracts, string $phase,
        int $size, array &$lease, ?callable $hook): void
    {
        $ordinal = 0;
        $batch = [];
        $prefixCursor = null;
        $expected = (int) $context['table_counts'][$mapping['source_table']];
        $prefixDigest = hash_init('sha256');
        $resumeCount = $lease['count'];
        foreach ($reader->rows($mapping['source_table']) as $row) {
            $ordinal++;
            $this->validateRow($context, $mapping, $row, $ordinal);
            if ($ordinal <= $lease['count']) {
                // Resume verifies every skipped row; a checkpoint is no oracle.
                $this->writer->write($context, $mapping, $row, true);
                if ($ordinal % 100 === 0) {
                    $this->checkpoints->renew($lease);
                }
                if ($phase === 'relationships') {
                    $this->db->transaction(function () use ($context, $mapping, $row, $contracts, $lease): void {
                        $this->db->table('sources')->where('id', $context['source_id'])->lockForUpdate()->first();
                        $this->checkpoints->lock($lease);
                        $this->relationships->resolve($context, $mapping, $row, $contracts);
                    });
                }
                if ($ordinal > $resumeCount - $lease['batch_count']) {
                    $this->digestRow($prefixDigest, $mapping, $row);
                }
                $prefixCursor = $row['cursor'];
                if ($ordinal === $lease['count'] && ($lease['cursor'] !== $prefixCursor
                    || $lease['cursor_hash'] !== hash('sha256', $prefixCursor, true)
                    || $lease['batch_hash'] !== hash_final($prefixDigest, true))) {
                    throw new RuntimeException('Checkpoint cursor does not match immutable occurrence order.');
                }

                continue;
            }
            $batch[] = $row;
            if (count($batch) === $size) {
                $this->batch($context, $mapping, $contracts, $phase, $batch, $lease, $ordinal === $expected, $hook);
                $batch = [];
            }
        }
        if ($ordinal !== $expected || $lease['count'] > $ordinal) {
            throw new RuntimeException('Frozen table count or contiguous checkpoint boundary mismatch.');
        }
        if ($expected === 0 && $lease['status'] === 'complete'
            && ($lease['cursor'] !== '' || $lease['cursor_hash'] !== hash('sha256', '', true)
                || $lease['batch_hash'] !== hash('sha256', '', true) || $lease['batch_count'] !== 0)) {
            throw new RuntimeException('Empty-table checkpoint evidence is corrupt.');
        }
        if ($batch !== [] || ($expected === 0 && $lease['status'] !== 'complete')) {
            $this->batch($context, $mapping, $contracts, $phase, $batch, $lease, true, $hook);
        }
    }

    private function batch(array $context, array $mapping, array $contracts, string $phase, array $batch, array &$lease, bool $complete, ?callable $hook): void
    {
        $info = ['run_id' => $context['run_id'], 'table_id' => $mapping['source_table_id'], 'phase' => $phase,
            'checkpoint_id' => $lease['id'], 'fencing_token' => $lease['token'], 'batch_ordinal' => $lease['batch'] + 1];
        $hook && $hook('before_batch', $info);
        $next = $lease;
        $this->db->transaction(function () use ($context, $mapping, $contracts, $phase, $batch, &$next, $complete, $hook, $info): void {
            $this->db->table('sources')->where('id', $context['source_id'])->lockForUpdate()->first();
            $this->checkpoints->lock($next);
            $digest = hash_init('sha256');
            foreach ($batch as $row) {
                $this->digestRow($digest, $mapping, $row);
                if ($phase === 'preservation') {
                    $this->writer->write($context, $mapping, $row);
                } else {
                    $this->writer->write($context, $mapping, $row, true);
                    $this->relationships->resolve($context, $mapping, $row, $contracts);
                }
                $hook && $hook('after_row', $info + ['ordinal' => $row['ordinal']]);
            }
            $cursor = $batch === [] ? '' : $batch[array_key_last($batch)]['cursor'];
            $hook && $hook('before_commit', $info);
            $this->checkpoints->advance($next, count($batch), $cursor, hash_final($digest, true), $complete);
        });
        $lease = $next;
        $hook && $hook('after_commit', $info);
    }

    private function digestRow(mixed $digest, array $mapping, array $row): void
    {
        hash_update($digest, pack('N', strlen($row['key'])).$row['key']);
        foreach ($mapping['columns'] as $column) {
            $value = $row['values'][$column['source_column']];
            hash_update($digest, $value === null ? "\x00" : "\x01".pack('N', strlen($value)).$value);
        }
    }

    private function validateRow(array $context, array $mapping, array $row, int $ordinal): void
    {
        if (($row['ordinal'] ?? null) !== $ordinal || ! is_string($row['key'] ?? null) || $row['key'] === ''
            || ! is_string($row['cursor'] ?? null) || ! is_array($row['values'] ?? null)) {
            throw new RuntimeException('Invalid or noncontiguous frozen occurrence.');
        }
        $names = array_column($mapping['columns'], 'source_column');
        $actual = array_keys($row['values']);
        sort($names);
        sort($actual);
        if ($names !== $actual) {
            throw new RuntimeException('Frozen row does not contain exactly the approved source columns.');
        }
        foreach ($mapping['columns'] as $column) {
            $value = $row['values'][$column['source_column']];
            if ((! is_string($value) && $value !== null) || ($value === null && str_contains($column['source_definition'], 'NOT NULL'))) {
                throw new RuntimeException('Frozen source type/nullability invariant failed.');
            }
        }
        $pk = $mapping['legacy_primary_key'];
        $kind = $pk === [] ? 'ordinal' : 'primary';
        $definitions = [];
        foreach ($pk as $field) {
            $column = array_values(array_filter($mapping['columns'], fn ($c) => $c['source_column'] === $field))[0];
            $definitions[$field] = $column['source_definition'];
        }
        $expectedKey = $kind === 'primary' ? KeyEncoder::primary($definitions, $row['values'])
            : KeyEncoder::ordinal($context['sql_sha256'], $mapping['source_table'], $ordinal);
        if (($row['key_kind'] ?? null) !== $kind || $expectedKey !== $row['key']) {
            throw new RuntimeException('Frozen canonical source key does not match its raw occurrence.');
        }
    }

    private function preflight(FrozenSourceReader $reader): array
    {
        $context = $reader->contract();
        foreach (['archive_sha256', 'sql_sha256', 'frozen_manifest_sha256', 'mapping_sha256', 'schema_sha256', 'extractor_sha256'] as $field) {
            if (! is_string($context[$field] ?? null) || ! preg_match('/\A[0-9a-f]{64}\z/', $context[$field])) {
                throw new RuntimeException('Missing or invalid frozen import hash.');
            }
        }
        if ($context['mapping_sha256'] !== OperationalManifest::SHA256 || $context['schema_sha256'] !== OperationalManifest::SHA256
            || ($context['key_encoding_version'] ?? null) !== KeyEncoder::VERSION || ! is_string($context['extractor_version'] ?? null)
            || strlen($context['extractor_version']) < 1 || strlen($context['extractor_version']) > 100) {
            throw new RuntimeException('Frozen interpretation does not match approved schema/encoding.');
        }
        $source = $this->db->table('sources')->where('id', $context['source_id'] ?? 0)->first();
        $snapshot = $this->db->table('snapshots')->where('id', $context['snapshot_id'] ?? 0)->first();
        $city = $source ? $this->db->table('cities')->where('id', $source->city_id)->first() : null;
        if (! $snapshot || ! $source || ! $city || $city->name_en !== ($context['city'] ?? null)
            || (int) $snapshot->source_id !== (int) $source->id || bin2hex($snapshot->sql_sha256) !== $context['sql_sha256']
            || bin2hex($snapshot->archive_sha256) !== $context['archive_sha256']) {
            throw new RuntimeException('Frozen source/city/snapshot identity mismatch.');
        }
        $context['source_id'] = (int) $source->id;
        $context['snapshot_id'] = (int) $snapshot->id;
        $context['city_id'] = (int) $source->city_id;
        $expected = array_column(array_filter(OperationalManifest::load()['mappings'], fn ($m) => $m['city'] === $context['city']), 'source_table');
        $actual = $reader->tables();
        $counts = array_keys($context['table_counts'] ?? []);
        sort($expected);
        sort($actual);
        sort($counts);
        if ($expected !== $actual || $expected !== $counts || count(array_unique($actual)) !== count($actual)) {
            throw new RuntimeException('Frozen source does not cover every approved city table.');
        }
        foreach ($context['table_counts'] as $count) {
            if (! is_int($count) || $count < 0) {
                throw new RuntimeException('Frozen row counts must be nonnegative integers.');
            }
        }

        return $context;
    }

    private function run(array $context): int
    {
        return $this->db->transaction(function () use ($context): int {
            $this->db->table('snapshots')->where('id', $context['snapshot_id'])->lockForUpdate()->first();
            $frozen = ['snapshot_id' => $context['snapshot_id']];
            foreach (['frozen_manifest_sha256', 'mapping_sha256', 'schema_sha256', 'extractor_sha256'] as $field) {
                $frozen[$field] = hex2bin($context[$field]);
            }
            $frozen['extractor_version'] = $context['extractor_version'];
            $frozen['key_encoding_version'] = $context['key_encoding_version'];
            $existing = $this->db->table('migration_run_contracts')->where('snapshot_id', $context['snapshot_id'])
                ->where('mapping_sha256', $frozen['mapping_sha256'])->first();
            if ($existing) {
                foreach ($frozen as $key => $value) {
                    if ((string) $existing->{$key} !== (string) $value) {
                        throw new RuntimeException('Frozen run contract changed; cannot resume or reuse its lane.');
                    }
                }

                $run = (int) $existing->run_id;
                $this->db->table('migration_runs')->where('id', $run)->where('snapshot_id', $context['snapshot_id'])
                    ->update(['status' => 'running', 'completed_at' => null]);

                return $run;
            }
            $run = (int) $this->db->table('migration_runs')->insertGetId(['snapshot_id' => $context['snapshot_id'],
                'tool_version' => 'phase3-import-v1', 'started_at' => now('UTC'), 'status' => 'running']);
            $this->db->table('migration_run_contracts')->insert($frozen + ['run_id' => $run, 'created_at' => now('UTC')]);

            return $run;
        });
    }
}
