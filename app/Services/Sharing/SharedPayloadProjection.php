<?php

declare(strict_types=1);

namespace App\Services\Sharing;

use Database\Schema\OperationalManifest;
use Database\Schema\SharedCatalogManifest;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

/** Internal read projection. Callers authorize the scoped alias before returning data. */
final class SharedPayloadProjection
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function project(string $sourceTable, object $alias, int $sourceId, int $snapshotId): object
    {
        if ((int) ($alias->source_id ?? 0) !== $sourceId || $snapshotId < 1) {
            throw new RuntimeException('Shared projection requires the authorized alias source and snapshot.');
        }
        if (! property_exists($alias, 'shared_group_id')) {
            return clone $alias;
        }
        $pointers = [$alias->shared_group_id, $alias->shared_row_id, $alias->shared_snapshot_id];
        if (count(array_filter($pointers, fn ($value) => $value !== null)) === 0) {
            return clone $alias;
        }
        if (in_array(null, $pointers, true) || (int) $alias->shared_snapshot_id !== $snapshotId) {
            throw new RuntimeException('Shared alias pointers are incomplete or belong to another snapshot.');
        }
        require_once __DIR__.'/../../../database/schema/SharedCatalogManifest.php';
        $table = SharedCatalogManifest::table($sourceTable);
        $source = $this->db->table('sources')->where('id', $sourceId)->first();
        $city = $source ? $this->db->table('cities')->where('id', $source->city_id)->first() : null;
        if (! $city || (int) $alias->city_id !== (int) $source->city_id) {
            throw new RuntimeException('Shared alias city does not match its source.');
        }
        $mapping = OperationalManifest::mapping($city->name_en, $sourceTable);
        $tableId = (int) $mapping['source_table_id'];
        if ((int) $alias->source_table_id !== $tableId) {
            throw new RuntimeException('Shared alias has the wrong source-table kind.');
        }
        $membership = $this->db->table('shared_catalog_memberships')->where([
            'city_id' => (int) $source->city_id, 'source_id' => $sourceId, 'source_table_id' => $tableId,
            'snapshot_id' => $snapshotId, 'group_id' => $alias->shared_group_id,
        ])->first();
        $group = $this->db->table('shared_catalog_groups')->where('id', $alias->shared_group_id)->first();
        $origin = $this->db->table('operational_origins')->where(['entity_id' => $alias->id, 'source_id' => $sourceId,
            'source_table_id' => $tableId, 'snapshot_id' => $snapshotId])->first();
        if (! $membership || ! $group || ! $origin || $group->status !== 'published'
            || $group->published_at === null || $group->verified_evidence_locator === null
            || (int) $group->source_table_id !== $tableId || (int) $membership->row_count !== (int) $group->row_count) {
            throw new RuntimeException('Shared alias membership or published evidence is invalid.');
        }
        $approved = null;
        foreach (SharedCatalogManifest::groups($sourceTable) as $candidate) {
            if (in_array($city->name_en, $candidate['cities'], true)
                && hex2bin($candidate['schema_contract_sha256']) === $group->schema_contract_sha256
                && hex2bin($candidate['full_table_sha256']) === $group->full_table_sha256
                && $candidate['rows_per_city'] === (int) $group->row_count) {
                $approved = $candidate;
                break;
            }
        }
        if ($approved === null) {
            throw new RuntimeException('Shared group is outside the approved equality scope.');
        }
        $members = $this->db->table('shared_catalog_memberships as m')->join('sources as s', 's.id', '=', 'm.source_id')
            ->join('cities as c', 'c.id', '=', 's.city_id')->where('m.group_id', $group->id)->get(['m.*', 'c.name_en']);
        $cities = $members->pluck('name_en')->all();
        $expectedCities = $approved['cities'];
        sort($cities, SORT_STRING);
        sort($expectedCities, SORT_STRING);
        if ($cities !== $expectedCities || $this->db->table($table['name'])->where('group_id', $group->id)->count() !== (int) $group->row_count) {
            throw new RuntimeException('Published sharing group is no longer whole.');
        }
        $canonical = $this->db->table($table['name'])->where(['id' => $alias->shared_row_id,
            'group_id' => $alias->shared_group_id, 'source_table_id' => $tableId])->first();
        if (! $canonical) {
            throw new RuntimeException('Shared canonical row is missing.');
        }
        $columns = SharedRowCodec::columns($table);
        $payload = [];
        $projected = clone $alias;
        foreach ($columns as $column) {
            $name = $column['name'];
            if ($alias->{$name} !== null) {
                throw new RuntimeException('Shared alias still contains duplicate payload.');
            }
            $payload[$name] = $canonical->{$name};
            $projected->{$name} = $canonical->{$name};
        }
        if (hash('sha256', SharedRowCodec::encode($payload, $columns), true) !== $canonical->row_sha256) {
            throw new RuntimeException('Shared canonical row fingerprint is corrupt.');
        }
        $preserved = $this->db->table($mapping['preservation_table'])->where('record_id', $origin->source_record_id)->first();
        if (! $preserved) {
            throw new RuntimeException('Shared alias preserved occurrence is missing.');
        }
        $original = [];
        foreach ($mapping['columns'] as $column) {
            $original[$column['target_column']] = $preserved->{$column['preservation_column']};
        }
        if (SharedRowCodec::encode($original, $columns) !== SharedRowCodec::encode($payload, $columns)) {
            throw new RuntimeException('Shared alias canonical payload does not match its exact preserved occurrence.');
        }

        return $projected;
    }
}
