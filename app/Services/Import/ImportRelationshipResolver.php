<?php

declare(strict_types=1);

namespace App\Services\Import;

use Database\Schema\OperationalManifest;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

final class ImportRelationshipResolver
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function resolve(array $context, array $mapping, array $row, array $contracts): int
    {
        $child = $this->db->table('source_records')->where('source_id', $context['source_id'])
            ->where('snapshot_id', $context['snapshot_id'])->where('table_id', $mapping['source_table_id'])
            ->where('key_sha256', hash('sha256', $row['key'], true))->first();
        if (! $child || $child->source_key !== $row['key']) {
            throw new RuntimeException('Relationship child occurrence is missing or has a key collision.');
        }
        $childOrigin = $mapping['target_table'] === null ? null : $this->db->table('operational_origins')->where('source_record_id', $child->id)->first();
        foreach ($contracts as $contract) {
            $parentMapping = OperationalManifest::mapping($context['city'], $contract['parent_source_table']);
            $contractId = $this->contract($context, $mapping, $parentMapping, $contract);
            $values = array_map(fn ($field) => $row['values'][$field], $contract['child_columns']);
            $status = in_array(null, $values, true) ? 'null' : 'orphan';
            $parent = null;
            if ($status !== 'null') {
                $query = $this->db->table($parentMapping['preservation_table'].' as p')
                    ->join('source_records as r', 'r.id', '=', 'p.record_id')
                    ->where('r.source_id', $context['source_id'])->where('r.snapshot_id', $context['snapshot_id'])
                    ->where('r.table_id', $parentMapping['source_table_id']);
                foreach ($contract['parent_columns'] as $index => $field) {
                    $query->where('p.'.$field, $values[$index]);
                }
                $matches = $query->limit(2)->get(['r.id']);
                $status = $matches->count() === 1 ? 'resolved' : ($matches->isEmpty() ? 'orphan' : 'ambiguous');
                $parent = $status === 'resolved' ? (int) $matches[0]->id : null;
                // This exact message sentinel is documented by a source writer;
                // it remains distinct from NULL and must still emit an exception.
                if ($parent === null && $mapping['source_table'] === 'user_notification'
                    && $contract['child_columns'] === ['MicroProject_ID'] && $values === ['-1']) {
                    $status = 'sentinel';
                }
            }
            $ledger = ['contract_id' => $contractId, 'child_record_id' => (int) $child->id];
            $existing = $this->db->table('record_relationships')->where($ledger)->first();
            if ($existing) {
                if ($existing->status !== $status || ($existing->parent_record_id === null ? null : (int) $existing->parent_record_id) !== $parent) {
                    throw new RuntimeException('Previously resolved immutable relationship changed.');
                }
                $relationshipId = (int) $existing->id;
            } else {
                $relationshipId = (int) $this->db->table('record_relationships')->insertGetId($ledger + [
                    'source_id' => $context['source_id'], 'child_table_id' => $mapping['source_table_id'],
                    'parent_table_id' => $parentMapping['source_table_id'], 'parent_record_id' => $parent,
                    'status' => $status, 'evidence_locator' => 'import:relationship:'.$contract['id'],
                ]);
            }
            if ($mapping['target_table'] !== null) {
                if (! $childOrigin || (int) $childOrigin->snapshot_id !== $context['snapshot_id']) {
                    throw new RuntimeException('Relationship child operational origin is missing.');
                }
                $resolvedId = $parent;
                if ($parent !== null && $parentMapping['target_table'] !== null) {
                    $parentOrigin = $this->db->table('operational_origins')->where('source_record_id', $parent)->first();
                    if (! $parentOrigin || (int) $parentOrigin->snapshot_id !== $context['snapshot_id']
                        || (int) $parentOrigin->source_id !== $context['source_id']) {
                        throw new RuntimeException('Relationship parent operational origin has wrong snapshot/source.');
                    }
                    $resolvedId = (int) $parentOrigin->entity_id;
                }
                $current = $this->db->table($mapping['target_table'])->where('id', $childOrigin->entity_id)->first();
                if (! $current) {
                    throw new RuntimeException('Shared relationship child row is missing.');
                }
                $stateField = $contract['state_column'];
                $idField = $contract['resolved_column'];
                if ($current->{$stateField} !== 'pending'
                    && ($current->{$stateField} !== $status || ($current->{$idField} === null ? null : (int) $current->{$idField}) !== $resolvedId)) {
                    throw new RuntimeException('Shared immutable relationship changed on retry.');
                }
                $this->db->table($mapping['target_table'])->where('id', $childOrigin->entity_id)
                    ->update([$stateField => $status, $idField => $resolvedId]);
            }
            if (! in_array($status, ['resolved', 'null'], true)) {
                $this->exception($context, (int) $child->id, $relationshipId, 'relationship_'.$status);
            }
        }
        if ($mapping['source_table'] === 'user_notification') {
            $this->exception($context, (int) $child->id, null, 'notification_target_unresolved');
        }
        if ($mapping['source_table'] === 'microproject_ag_answer') {
            $this->agricultureCompatibility($context, $row, (int) $child->id);
        }
        $this->db->table('source_records')->where('id', $child->id)->update(['resolution_status' => 'staged']);

        return count($contracts);
    }

    private function contract(array $context, array $child, array $parent, array $contract): int
    {
        $key = ['source_id' => $context['source_id'], 'child_table_id' => $child['source_table_id'], 'name' => 'p3:'.$contract['id']];
        $declared = count(array_filter($contract['occurrences'], fn ($o) => $o['city'] === $context['city'] && $o['contract'] !== 'application-only')) > 0;
        $expected = ['parent_table_id' => $parent['source_table_id'], 'child_columns_json' => json_encode($contract['child_columns'], JSON_THROW_ON_ERROR),
            'parent_columns_json' => json_encode($contract['parent_columns'], JSON_THROW_ON_ERROR),
            'evidence_locator' => 'schema:operational-v1:'.$contract['id'], 'contract_kind' => $declared ? 'declared' : 'application'];
        $found = $this->db->table('relationship_contracts')->where($key)->first();
        if ($found) {
            if ((int) $found->parent_table_id !== $parent['source_table_id']
                || json_decode($found->child_columns_json, true, flags: JSON_THROW_ON_ERROR) !== $contract['child_columns']
                || json_decode($found->parent_columns_json, true, flags: JSON_THROW_ON_ERROR) !== $contract['parent_columns']
                || $found->contract_kind !== $expected['contract_kind']) {
                throw new RuntimeException('Persisted relationship contract changed.');
            }

            return (int) $found->id;
        }

        return (int) $this->db->table('relationship_contracts')->insertGetId($key + $expected);
    }

    private function exception(array $context, int $record, ?int $relationship, string $category): void
    {
        $key = ['run_id' => $context['run_id'], 'record_id' => $record, 'relationship_id' => $relationship, 'category' => $category];
        if (! $this->db->table('migration_exceptions')->where($key)->exists()) {
            $this->db->table('migration_exceptions')->insert($key + ['state' => 'open', 'evidence_locator' => 'import:exception:'.$record.':'.$category]);
        }
    }

    private function agricultureCompatibility(array $context, array $row, int $record): void
    {
        $mapping = OperationalManifest::mapping($context['city'], 'aganswer');
        $answers = $this->db->table($mapping['preservation_table'].' as a')->join('source_records as r', 'r.id', '=', 'a.record_id')
            ->where('r.source_id', $context['source_id'])->where('r.snapshot_id', $context['snapshot_id'])
            ->where('a.ID', $row['values']['Answer_ID'])->limit(2)->get(['a.Question_ID']);
        if ($answers->count() === 1 && ! ExactValue::equal($answers[0]->Question_ID, $row['values']['Question_ID'], 'int')) {
            $this->exception($context, $record, null, 'agriculture_answer_question_mismatch');
        }
    }
}
