<?php

declare(strict_types=1);

namespace App\Services\Reconciliation;

use App\Services\Import\FrozenSourceReader;
use Database\Schema\OperationalManifest;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Reads frozen source occurrences and both target projections independently of import code. */
final class IndependentReconciler
{
    public function __construct(private CellComparator $cells, private SourceKeyInspector $keys) {}

    public function reconcile(FrozenSourceReader $reader): array
    {
        if (DB::connection()->getDriverName() !== 'mysql' || DB::transactionLevel() !== 0) {
            throw new RuntimeException('Reconciliation requires a separate MySQL consistent read transaction.');
        }
        DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');

        return DB::transaction(fn (): array => $this->compare($reader));
    }

    private function compare(FrozenSourceReader $reader): array
    {
        $contract = $reader->contract();
        require_once database_path('schema/OperationalManifest.php');
        $schema = OperationalManifest::load();
        $mappings = [];
        foreach ($schema['mappings'] as $mapping) {
            if ($mapping['city'] === $contract['city']) {
                $mappings[$mapping['source_table']] = $mapping;
            }
        }
        $names = array_keys($mappings);
        $supplied = $reader->tables();
        sort($names);
        sort($supplied);
        $city = DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')->where('s.id', $contract['source_id'])->first(['c.id', 'c.name_en']);
        $snapshot = DB::table('snapshots')->where('id', $contract['snapshot_id'])->first();
        if ($names !== $supplied || $city === null || $city->name_en !== $contract['city'] || $snapshot === null || (int) $snapshot->source_id !== (int) $contract['source_id']
            || bin2hex($snapshot->sql_sha256) !== $contract['sql_sha256'] || bin2hex($snapshot->archive_sha256) !== $contract['archive_sha256']
            || $contract['schema_sha256'] !== OperationalManifest::SHA256 || $contract['mapping_sha256'] !== OperationalManifest::SHA256
            || $contract['key_encoding_version'] !== 'length-prefix-v1') {
            throw new RuntimeException('Reconciliation source coverage or snapshot scope is invalid.');
        }
        $report = ['snapshot_id' => (int) $contract['snapshot_id'], 'source_id' => (int) $contract['source_id'], 'tables' => [], 'mismatch_count' => 0, 'mismatches' => [], 'source_anomalies' => 0, 'checked_cells' => 0, 'checked_relationships' => 0];
        foreach ($mappings as $name => $mapping) {
            $seen = [];
            $expectedCount = 0;
            $ordinalRecords = [];
            if ($mapping['legacy_primary_key'] === []) {
                foreach ($this->registry($contract, $mapping)->cursor() as $record) {
                    try {
                        $ordinal = $this->keys->ordinal($record->source_key, $contract['sql_sha256'], $name);
                        if (isset($ordinalRecords[$ordinal])) {
                            $this->issue($report, $name, $ordinal, 'duplicate_ordinal');
                        }
                        $ordinalRecords[$ordinal] = (int) $record->id;
                    } catch (RuntimeException) {
                        $this->issue($report, $name, 0, 'invalid_ordinal_key');
                    }
                }
            }
            foreach ($reader->rows($name) as $row) {
                $expectedCount++;
                $ordinal = (int) $row['ordinal'];
                if ($ordinal !== $expectedCount) {
                    throw new RuntimeException('Frozen source ordinals are not contiguous.');
                }
                $query = $this->typed($contract, $mapping);
                if ($mapping['legacy_primary_key'] === []) {
                    $query->where('p.record_id', $ordinalRecords[$ordinal] ?? 0);
                } else {
                    foreach ($mapping['legacy_primary_key'] as $column) {
                        $query->where('p.'.$column, $row['values'][$column]);
                    }
                }
                $matches = $query->limit(2)->get();
                if ($matches->count() !== 1) {
                    $this->issue($report, $name, $ordinal, 'preservation_identity_cardinality');

                    continue;
                }
                $preserved = $matches->first();
                $recordId = (int) $preserved->record_id;
                if (isset($seen[$recordId])) {
                    $this->issue($report, $name, $ordinal, 'collapsed_source_occurrences');
                }
                $seen[$recordId] = true;
                $record = $this->registry($contract, $mapping)->where('id', $recordId)->first();
                $expectedKind = $mapping['legacy_primary_key'] === [] ? 'ordinal' : 'primary';
                if ($record === null || $record->key_kind !== $expectedKind || ! hash_equals(hash('sha256', $record->source_key, true), $record->key_sha256)) {
                    $this->issue($report, $name, $ordinal, 'registry_key_integrity');
                }
                if ($record !== null && (int) DB::table('migration_runs')->where('id', $record->import_run_id)->value('snapshot_id') !== (int) $contract['snapshot_id']) {
                    $this->issue($report, $name, $ordinal, 'record_run_snapshot');
                }
                if ($record !== null && $expectedKind === 'primary') {
                    $columns = [];
                    foreach ($mapping['legacy_primary_key'] as $column) {
                        $columns[$column] = $this->column($mapping, $column)['source_definition'];
                    }
                    if (! $this->keys->primaryMatches($record->source_key, $columns, $row['values'])) {
                        $this->issue($report, $name, $ordinal, 'registry_primary_key');
                    }
                }
                $this->compareProjection($report, $mapping, $row, $preserved, false);
                if ($mapping['target_table'] !== null) {
                    $origin = DB::table('operational_origins')->where('source_record_id', $recordId)->first();
                    if ($origin === null || (int) $origin->snapshot_id !== (int) $contract['snapshot_id'] || (int) $origin->source_id !== (int) $contract['source_id'] || (int) $origin->source_table_id !== (int) $mapping['source_table_id']) {
                        $this->issue($report, $name, $ordinal, 'origin_scope');

                        continue;
                    }
                    $operational = DB::table($mapping['target_table'])->where('id', $origin->entity_id)->first();
                    $entity = DB::table('operational_entities')->where('id', $origin->entity_id)->first();
                    if ($operational === null || $entity === null || (int) $operational->source_id !== (int) $contract['source_id'] || (int) $operational->city_id !== (int) $city->id || (int) $entity->city_id !== (int) $city->id || (int) $operational->source_table_id !== (int) $mapping['source_table_id']) {
                        $this->issue($report, $name, $ordinal, 'operational_identity');

                        continue;
                    }
                    if ($entity->identity_kind !== ($expectedKind === 'primary' ? 'legacy_primary' : 'legacy_ordinal')
                        || ($expectedKind === 'primary' && ($entity->first_key_bytes !== $record->source_key || $entity->first_key_sha256 !== hash('sha256', $record->source_key, true)))
                        || ($expectedKind === 'ordinal' && ($entity->first_key_bytes !== null || $entity->first_key_sha256 !== null))) {
                        $this->issue($report, $name, $ordinal, 'entity_original_identity');
                    }
                    $this->compareProjection($report, $mapping, $row, $operational, true);
                    foreach ($schema['relationships'] as $relationship) {
                        if ($relationship['child_source_table'] === $name
                            && count(array_filter($relationship['occurrences'], fn (array $item): bool => $item['city'] === $contract['city'])) === 0
                            && ($operational->{$relationship['state_column']} !== 'not_applicable' || $operational->{$relationship['resolved_column']} !== null)) {
                            $this->issue($report, $name, $ordinal, 'inapplicable_relationship');
                        }
                    }
                    $applicable = array_column($mapping['columns'], 'target_column');
                    foreach ($schema['tables'] as $table) {
                        if ($table['name'] === $mapping['target_table']) {
                            foreach ($table['columns'] as $column) {
                                if (str_starts_with($column['name'], 'c_') && ! in_array($column['name'], $applicable, true) && $operational->{$column['name']} !== null) {
                                    $this->issue($report, $name, $ordinal, 'inapplicable_variant_not_null', $column['name']);
                                }
                            }
                            break;
                        }
                    }
                } elseif (DB::table('operational_origins')->where('source_record_id', $recordId)->exists()) {
                    $this->issue($report, $name, $ordinal, 'unexpected_archive_origin');
                }
            }
            $actual = $this->registry($contract, $mapping)->count();
            $typed = $this->typed($contract, $mapping)->count();
            if ($actual !== $expectedCount || $typed !== $expectedCount || count($seen) !== $expectedCount || $expectedCount !== (int) ($contract['table_counts'][$name] ?? -1)) {
                $this->issue($report, $name, 0, 'table_count');
            }
            if ($mapping['target_table'] !== null) {
                $origins = DB::table('operational_origins')->where('snapshot_id', $contract['snapshot_id'])->where('source_id', $contract['source_id'])->where('source_table_id', $mapping['source_table_id'])->count();
                if ($origins !== $expectedCount) {
                    $this->issue($report, $name, 0, 'origin_count');
                }
            }
            $report['tables'][$name] = ['expected' => $expectedCount, 'registry' => $actual, 'preservation' => $typed];
        }
        $this->relationships($reader, $schema, $mappings, $report);
        $report['matches'] = $report['mismatch_count'] === 0;
        $report['core_ready'] = $report['matches'] && $report['source_anomalies'] === 0;
        $report['publishable'] = false;
        $report['target_read'] = 'read-only repeatable-read transaction';
        $report['coverage'] = 'source/preservation/shared cells, occurrence counts and mapped relationships; external files and report totals not certified';

        return $report;
    }

    private function relationships(FrozenSourceReader $reader, array $schema, array $mappings, array &$report): void
    {
        $contract = $reader->contract();
        foreach ($schema['relationships'] as $relationship) {
            $occurrences = array_values(array_filter($relationship['occurrences'], fn (array $item): bool => $item['city'] === $contract['city']));
            if ($occurrences === []) {
                continue;
            }
            $childMapping = $mappings[$relationship['child_source_table']];
            $parentMapping = $mappings[$relationship['parent_source_table']];
            foreach ($reader->rows($relationship['child_source_table']) as $row) {
                $child = $this->typed($contract, $childMapping);
                foreach ($childMapping['legacy_primary_key'] as $column) {
                    $child->where('p.'.$column, $row['values'][$column]);
                }
                $childRecord = $child->first();
                if ($childRecord === null) {
                    continue;
                }
                $parent = $this->typed($contract, $parentMapping);
                $null = false;
                foreach ($relationship['child_columns'] as $index => $column) {
                    $value = $row['values'][$column];
                    $null = $null || $value === null;
                    $parent->where('p.'.$relationship['parent_columns'][$index], $value);
                }
                $parents = $null ? collect() : $parent->limit(2)->get();
                $expectedState = $null ? 'null' : ($parents->count() === 1 ? 'resolved' : ($parents->isEmpty() ? 'orphan' : 'ambiguous'));
                if ($expectedState === 'orphan' && $relationship['child_source_table'] === 'user_notification' && $relationship['child_columns'] === ['MicroProject_ID'] && $row['values']['MicroProject_ID'] === '-1') {
                    $expectedState = 'sentinel';
                }
                $expectedId = $expectedState === 'resolved' ? (int) $parents->first()->record_id : null;
                foreach ($occurrences as $occurrence) {
                    $report['checked_relationships']++;
                    $ledgerContract = DB::table('relationship_contracts')->where('source_id', $contract['source_id'])->where('child_table_id', $childMapping['source_table_id'])->where('name', 'p3:'.$relationship['id'])->first();
                    $ledger = $ledgerContract === null ? null : DB::table('record_relationships')->where('contract_id', $ledgerContract->id)->where('child_record_id', $childRecord->record_id)->first();
                    if ($ledger === null || $ledger->status !== $expectedState || ($ledger->parent_record_id === null ? null : (int) $ledger->parent_record_id) !== $expectedId) {
                        $this->issue($report, $relationship['child_source_table'], $row['ordinal'], 'relationship_ledger');
                    }
                }
                if (in_array($expectedState, ['orphan', 'ambiguous', 'sentinel'], true)) {
                    $report['source_anomalies']++;
                }
                if ($childMapping['target_table'] !== null) {
                    $childOrigin = DB::table('operational_origins')->where('source_record_id', $childRecord->record_id)->first();
                    $shared = $childOrigin === null ? null : DB::table($childMapping['target_table'])->where('id', $childOrigin->entity_id)->first();
                    $parentId = $expectedId;
                    if ($expectedId !== null && $parentMapping['target_table'] !== null) {
                        $parentId = DB::table('operational_origins')->where('source_record_id', $expectedId)->where('snapshot_id', $contract['snapshot_id'])->value('entity_id');
                        $parentId = $parentId === null ? null : (int) $parentId;
                    }
                    $column = $relationship['resolved_column'];
                    $state = $relationship['state_column'];
                    if ($shared === null || $shared->{$state} !== $expectedState || ($shared->{$column} === null ? null : (int) $shared->{$column}) !== $parentId) {
                        $this->issue($report, $relationship['child_source_table'], $row['ordinal'], 'shared_relationship');
                    }
                }
            }
        }
        // An overloaded notification target is unresolved even when every declared FK matches.
        foreach ($reader->rows('user_notification') as $row) {
            $report['source_anomalies']++;
        }
        // Validate the independent answer/question compatibility rule from source occurrences.
        foreach ($reader->rows('microproject_ag_answer') as $row) {
            foreach ($reader->rows('aganswer') as $answer) {
                if ($this->cells->equal($row['values']['Answer_ID'], $answer['values']['ID'], 'int')
                    && ! $this->cells->equal($row['values']['Question_ID'], $answer['values']['Question_ID'], 'int')) {
                    $report['source_anomalies']++;
                }
            }
        }
    }

    private function compareProjection(array &$report, array $mapping, array $row, object $actual, bool $operational): void
    {
        foreach ($mapping['columns'] as $column) {
            $report['checked_cells']++;
            $target = $operational ? $column['target_column'] : $column['preservation_column'];
            if (! $this->cells->equal($row['values'][$column['source_column']], $actual->{$target}, $column['source_definition'])) {
                $this->issue($report, $mapping['source_table'], $row['ordinal'], $operational ? 'operational_value' : 'preservation_value', $column['source_column']);
            }
        }
    }

    private function typed(array $contract, array $mapping): Builder
    {
        return DB::table($mapping['preservation_table'].' as p')->join('source_records as r', 'r.id', '=', 'p.record_id')
            ->where('r.source_id', $contract['source_id'])->where('r.snapshot_id', $contract['snapshot_id'])->where('r.table_id', $mapping['source_table_id'])->select('p.*');
    }

    private function registry(array $contract, array $mapping): Builder
    {
        return DB::table('source_records')->where('source_id', $contract['source_id'])->where('snapshot_id', $contract['snapshot_id'])->where('table_id', $mapping['source_table_id']);
    }

    private function column(array $mapping, string $name): array
    {
        foreach ($mapping['columns'] as $column) {
            if ($column['source_column'] === $name) {
                return $column;
            }
        }
        throw new RuntimeException('Unmapped original key column.');
    }

    private function issue(array &$report, string $table, int $ordinal, string $category, ?string $column = null): void
    {
        $report['mismatch_count']++;
        if (count($report['mismatches']) < 1000) {
            $report['mismatches'][] = compact('table', 'ordinal', 'category', 'column');
        }
    }
}
