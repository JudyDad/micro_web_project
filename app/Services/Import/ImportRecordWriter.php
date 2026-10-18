<?php

declare(strict_types=1);

namespace App\Services\Import;

use Database\Schema\OperationalManifest;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

final class ImportRecordWriter
{
    private array $payloadColumns = [];

    private array $inactiveReferences = [];

    public function __construct(private readonly ConnectionInterface $db)
    {
        $schema = OperationalManifest::load();
        foreach ($schema['tables'] as $table) {
            $this->payloadColumns[$table['name']] = array_values(array_filter(array_column($table['columns'], 'name'),
                fn ($name) => str_starts_with($name, 'c_')));
        }
        foreach ($schema['mappings'] as $mapping) {
            if ($mapping['target_table'] === null) {
                continue;
            }
            foreach ($schema['relationships'] as $relationship) {
                if ($relationship['target_table'] === $mapping['target_table']
                    && count(array_filter($relationship['occurrences'], fn ($o) => $o['city'] === $mapping['city'])) === 0) {
                    $this->inactiveReferences[$mapping['city']][$mapping['target_table']][$relationship['state_column']] = $relationship['resolved_column'];
                }
            }
        }
    }

    public function write(array $context, array $mapping, array $row, bool $verifyOnly = false): int
    {
        $identity = ['source_id' => $context['source_id'], 'table_id' => $mapping['source_table_id'],
            'snapshot_id' => $context['snapshot_id'], 'key_sha256' => hash('sha256', $row['key'], true)];
        $record = $this->db->table('source_records')->where($identity)->first();
        if ($record && $record->source_key !== $row['key']) {
            throw new RuntimeException('Source key digest collision.');
        }
        if ($verifyOnly && ! $record) {
            throw new RuntimeException('Checkpoint skips a missing preserved occurrence.');
        }
        if ($record) {
            $id = (int) $record->id;
            if (! $this->db->table('migration_runs')->where('id', $record->import_run_id)->where('snapshot_id', $context['snapshot_id'])->exists()) {
                throw new RuntimeException('Preserved occurrence cites an import run from another snapshot.');
            }
            if ($record->key_kind !== $row['key_kind']) {
                throw new RuntimeException('Preserved occurrence key kind changed.');
            }
        } else {
            $id = (int) $this->db->table('source_records')->insertGetId($identity + [
                'source_key' => $row['key'], 'key_kind' => $row['key_kind'], 'import_run_id' => $context['run_id'], 'resolution_status' => 'pending',
            ]);
        }
        $preserved = ['record_id' => $id, 'origin_source_id' => $context['source_id'], 'origin_table_id' => $mapping['source_table_id']];
        foreach ($mapping['columns'] as $column) {
            $preserved[$column['preservation_column']] = $row['values'][$column['source_column']];
        }
        if ($record) {
            $this->verify($mapping['preservation_table'], 'record_id', $id, $mapping['columns'], $row['values'], false);
        } else {
            $this->db->table($mapping['preservation_table'])->insert($preserved);
            $this->assertNoWarnings();
            $this->verify($mapping['preservation_table'], 'record_id', $id, $mapping['columns'], $row['values'], false);
        }
        if ($mapping['target_table'] === null) {
            return $id;
        }
        $origin = $this->db->table('operational_origins')->where('source_record_id', $id)->first();
        if ($origin) {
            if ((int) $origin->source_id !== $context['source_id'] || (int) $origin->snapshot_id !== $context['snapshot_id']
                || (int) $origin->source_table_id !== $mapping['source_table_id']) {
                throw new RuntimeException('Operational origin scope mismatch.');
            }
            $entity = (int) $origin->entity_id;
            $identityRow = $this->db->table('operational_entities')->where('id', $entity)->first();
            $primary = $row['key_kind'] === 'primary';
            if (! $identityRow || (int) $identityRow->city_id !== $context['city_id']
                || (int) $identityRow->source_id !== $context['source_id']
                || (int) $identityRow->source_table_id !== $mapping['source_table_id']
                || $identityRow->identity_kind !== ($primary ? 'legacy_primary' : 'legacy_ordinal')
                || $identityRow->first_key_bytes !== ($primary ? $row['key'] : null)
                || $identityRow->first_key_sha256 !== ($primary ? hash('sha256', $row['key'], true) : null)
                || ! $this->db->table('migration_runs')->where('id', $origin->linked_by_run_id)->where('snapshot_id', $context['snapshot_id'])->exists()) {
                throw new RuntimeException('Operational entity or origin identity evidence is corrupt.');
            }
            $this->verify($mapping['target_table'], 'id', $entity, $mapping['columns'], $row['values'], true);
            $inactive = $this->inactiveReferences[$context['city']][$mapping['target_table']] ?? [];
            if ($inactive !== []) {
                $shared = $this->db->table($mapping['target_table'])->where('id', $entity)->first();
                foreach ($inactive as $state => $resolved) {
                    if ($shared->{$state} !== 'not_applicable' || $shared->{$resolved} !== null) {
                        throw new RuntimeException('Inapplicable source relationship contains an invented shared link.');
                    }
                }
            }
        } else {
            if ($verifyOnly || ($record && (int) $record->import_run_id === $context['run_id'])) {
                throw new RuntimeException('Completed preservation occurrence lacks operational origin; explicit recovery required.');
            }
            $keyHash = $row['key_kind'] === 'primary' ? hash('sha256', $row['key'], true) : null;
            if ($keyHash !== null) {
                $previous = $this->db->table('operational_entities')->where('source_id', $context['source_id'])
                    ->where('source_table_id', $mapping['source_table_id'])->where('first_key_sha256', $keyHash)->first();
                if ($previous) {
                    if ($previous->first_key_bytes !== $row['key']) {
                        throw new RuntimeException('Operational key digest collision.');
                    }
                    throw new RuntimeException('Replacement snapshot identity needs an explicit reviewed continuity decision.');
                }
            }
            $entity = (int) $this->db->table('operational_entities')->insertGetId([
                'city_id' => $context['city_id'], 'source_id' => $context['source_id'], 'source_table_id' => $mapping['source_table_id'],
                'identity_kind' => $row['key_kind'] === 'primary' ? 'legacy_primary' : 'legacy_ordinal',
                'first_key_sha256' => $keyHash, 'first_key_bytes' => $keyHash === null ? null : $row['key'], 'created_at' => now('UTC'),
            ]);
            $projection = ['id' => $entity, 'city_id' => $context['city_id'], 'source_id' => $context['source_id'], 'source_table_id' => $mapping['source_table_id']];
            foreach ($mapping['columns'] as $column) {
                $projection[$column['target_column']] = $row['values'][$column['source_column']];
            }
            foreach ($this->inactiveReferences[$context['city']][$mapping['target_table']] ?? [] as $state => $resolved) {
                $projection[$state] = 'not_applicable';
                $projection[$resolved] = null;
            }
            $this->db->table($mapping['target_table'])->insert($projection);
            $this->assertNoWarnings();
            $this->db->table('operational_origins')->insert([
                'source_record_id' => $id, 'source_id' => $context['source_id'], 'source_table_id' => $mapping['source_table_id'],
                'snapshot_id' => $context['snapshot_id'], 'entity_id' => $entity, 'linked_by_run_id' => $context['run_id'], 'linked_at' => now('UTC'),
            ]);
            $this->verify($mapping['target_table'], 'id', $entity, $mapping['columns'], $row['values'], true);
        }

        return $id;
    }

    private function verify(string $table, string $idColumn, int $id, array $columns, array $values, bool $operational): void
    {
        $stored = $this->db->table($table)->where($idColumn, $id)->first();
        if (! $stored) {
            throw new RuntimeException('Mapped typed row is missing.');
        }
        foreach ($columns as $column) {
            $target = $column[$operational ? 'target_column' : 'preservation_column'];
            if (! ExactValue::equal($stored->{$target}, $values[$column['source_column']], $column['source_definition'])) {
                throw new RuntimeException('Exact typed row roundtrip or retry validation failed.');
            }
        }
        if ($operational) {
            $mapped = array_column($columns, 'target_column');
            foreach (array_diff($this->payloadColumns[$table], $mapped) as $inapplicable) {
                if ($stored->{$inapplicable} !== null) {
                    throw new RuntimeException('Inapplicable shared source-column variant is non-NULL.');
                }
            }
        }
    }

    private function assertNoWarnings(): void
    {
        // PDO::query also prepares on mysqlnd when emulation is disabled.
        // Send this static diagnostic through the text protocol so it cannot
        // manufacture warning 1295 and replace the insert's diagnostics.
        $pdo = $this->db->getPdo();
        $emulate = $pdo->getAttribute(\PDO::ATTR_EMULATE_PREPARES);
        try {
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            $warnings = $pdo->query('SHOW WARNINGS')->fetchAll(\PDO::FETCH_OBJ);
        } finally {
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $emulate);
        }
        if ($warnings !== []) {
            $codes = array_map(static fn (object $warning): int => (int) $warning->Code, $warnings);
            throw new RuntimeException('Target warned while storing exact source values; batch rolled back. Codes: '.implode(',', $codes));
        }
    }
}
