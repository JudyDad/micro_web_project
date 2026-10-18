<?php

declare(strict_types=1);

namespace App\Services\Sharing;

use App\Services\Import\FrozenSourceReader;
use App\Services\Reconciliation\IndependentReconciler;
use Database\Schema\OperationalManifest;
use Database\Schema\SharedCatalogManifest;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

require_once __DIR__.'/../../../database/schema/SharedCatalogManifest.php';

/** Publishes complete immutable equality groups; preservation and entity IDs survive. */
final class SharedCatalogConverter
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly CatalogSourceProof $proof,
        private readonly IndependentReconciler $reconciler,
    ) {}

    public function convert(array $readersByCity, string $decisionLocator, string $evidenceLocator, ?callable $hook = null, ?array $groups = null): array
    {
        $this->guard($decisionLocator, $evidenceLocator);
        $groups ??= SharedCatalogManifest::groups();
        $seen = [];
        foreach ($groups as $group) {
            $approved = SharedCatalogManifest::approvedGroup($group['table'] ?? '', $group['cities'] ?? []);
            if ($approved !== $group || isset($seen[$group['table'].':'.$group['full_table_sha256']])) {
                throw new RuntimeException('Conversion accepts each exact approved whole-table group once.');
            }
            $seen[$group['table'].':'.$group['full_table_sha256']] = true;
        }
        $order = array_flip(OperationalManifest::load()['parent_first_order']);
        usort($groups, fn ($a, $b) => [$order['op_'.$a['table']], $a['full_table_sha256']] <=> [$order['op_'.$b['table']], $b['full_table_sha256']]);
        $result = ['groups' => 0, 'canonical_rows' => 0, 'aliases' => 0, 'published_groups' => 0, 'reused_groups' => 0];
        foreach ($groups as $group) {
            $proof = $this->proof->verify($group, $readersByCity);
            $contexts = $this->contexts($group, $readersByCity, $proof);
            $conversion = $this->db->transaction(function () use ($group, $readersByCity, $contexts, $decisionLocator, $evidenceLocator, $hook): array {
                // Every importer and future catalog writer must take the same
                // source locks before checkpoint/record locks.
                $sources = array_column($contexts, 'source_id');
                sort($sources, SORT_NUMERIC);
                foreach ($sources as $source) {
                    if (! $this->db->table('sources')->where('id', $source)->lockForUpdate()->first()) {
                        throw new RuntimeException('Sharing source scope disappeared.');
                    }
                }
                foreach ($contexts as $city => $context) {
                    $this->checkSource($context);
                    $this->verify($readersByCity[$city], $group['table']);
                }
                $this->parents($group, $contexts, $readersByCity);
                $table = SharedCatalogManifest::table($group['table']);
                $columns = SharedRowCodec::columns($table);
                $members = [];
                $reference = null;
                foreach ($contexts as $city => $context) {
                    $members[$city] = $this->rows($group, $readersByCity[$city], $context, $columns);
                    $bytes = array_column($members[$city], 'bytes');
                    if ($reference !== null && $reference !== $bytes) {
                        throw new RuntimeException('An entire member table differs; no subset can be shared.');
                    }
                    $reference = $bytes;
                }
                $first = $members[array_key_first($members)];
                $mapping = OperationalManifest::mapping(array_key_first($contexts), $group['table']);
                $identity = ['source_table_id' => $mapping['source_table_id'],
                    'schema_contract_sha256' => hex2bin($group['schema_contract_sha256']),
                    'full_table_sha256' => hex2bin($group['full_table_sha256']), 'row_count' => $group['rows_per_city']];
                $stored = $this->db->table('shared_catalog_groups')->where($identity)->lockForUpdate()->first();
                $reused = $stored !== null;
                if ($stored && ($stored->status !== 'published' || $stored->verified_evidence_locator === null || $stored->published_at === null)) {
                    throw new RuntimeException('Unpublished sharing evidence requires explicit recovery.');
                }
                $groupId = $stored ? (int) $stored->id : (int) $this->db->table('shared_catalog_groups')->insertGetId($identity + [
                    'status' => 'staging', 'decision_locator' => $decisionLocator, 'created_at' => now('UTC')->format('Y-m-d H:i:s.u'),
                ]);
                $canonical = $this->canonical($table, $groupId, $mapping['source_table_id'], $first, $columns, $reused);
                foreach ($contexts as $city => $context) {
                    $this->membership($groupId, $mapping['source_table_id'], $group['rows_per_city'], $context, $evidenceLocator, $reused);
                    foreach ($members[$city] as $index => $row) {
                        $alias = $this->db->table($context['mapping']['target_table'])->where('id', $row['entity_id'])->lockForUpdate()->first();
                        if (! $alias) {
                            throw new RuntimeException('Sharing alias disappeared.');
                        }
                        if ($reused) {
                            if ((int) $alias->shared_group_id !== $groupId || (int) $alias->shared_row_id !== $canonical[$index]
                                || (int) $alias->shared_snapshot_id !== $context['snapshot_id']) {
                                throw new RuntimeException('Published whole-table alias mapping changed.');
                            }
                        } else {
                            if ($alias->shared_group_id !== null || $alias->shared_row_id !== null || $alias->shared_snapshot_id !== null) {
                                throw new RuntimeException('Alias is already assigned to another immutable sharing group.');
                            }
                            $update = ['shared_group_id' => $groupId, 'shared_row_id' => $canonical[$index], 'shared_snapshot_id' => $context['snapshot_id']];
                            foreach ($columns as $column) {
                                $update[$column['name']] = null;
                            }
                            $this->db->table($context['mapping']['target_table'])->where('id', $row['entity_id'])->update($update);
                        }
                    }
                }
                if (! $reused) {
                    $this->db->table('shared_catalog_groups')->where('id', $groupId)->update([
                        'status' => 'published', 'verified_evidence_locator' => $evidenceLocator, 'published_at' => now('UTC')->format('Y-m-d H:i:s.u'),
                    ]);
                }
                $this->complete($group, $groupId, $contexts, $table);
                foreach ($contexts as $city => $context) {
                    $this->verify($readersByCity[$city], $group['table']);
                }
                $info = ['group_id' => $groupId, 'table' => $group['table'], 'reused' => $reused];
                $hook && $hook('before_commit', $info);

                return $info;
            });
            $hook && $hook('after_commit', $conversion);
            $result['groups']++;
            $result['canonical_rows'] += $group['rows_per_city'];
            $result['aliases'] += $group['rows_per_city'] * count($group['cities']);
            $result[$conversion['reused'] ? 'reused_groups' : 'published_groups']++;
        }

        return $result;
    }

    private function guard(string $decision, string $evidence): void
    {
        $config = $this->db->getConfig();
        if ($this->db !== DB::connection() || $this->db->getDriverName() !== 'mysql' || $this->db->transactionLevel() !== 0 || ! app()->environment(['local', 'testing'])
            || ! in_array($config['database'] ?? null, ['micro_projects_local', 'micro_projects_test'], true)
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Sharing requires an isolated loopback target and its own transaction.');
        }
        foreach ([$decision, $evidence] as $locator) {
            if ($locator === '' || strlen($locator) > 1024 || preg_match('/[\x00-\x1f\x7f]/', $locator)) {
                throw new RuntimeException('Sharing requires sanitized bounded decision and independent evidence locators.');
            }
        }
    }

    private function contexts(array $group, array $readers, array $proof): array
    {
        if (($proof['verified'] ?? null) !== true || ($proof['full_table_sha256'] ?? null) !== $group['full_table_sha256']
            || ($proof['schema_contract_sha256'] ?? null) !== $group['schema_contract_sha256']) {
            throw new RuntimeException('Independent immutable archive evidence does not match the approved equality group.');
        }
        $contexts = [];
        foreach ($group['cities'] as $city) {
            if (! ($readers[$city] ?? null) instanceof FrozenSourceReader) {
                throw new RuntimeException('Every complete equality-group city requires a verified source reader.');
            }
            $context = $readers[$city]->contract();
            $expected = $proof['sources'][$city] ?? [];
            foreach (['archive_sha256', 'sql_sha256', 'frozen_manifest_sha256', 'snapshot_id'] as $field) {
                if (! array_key_exists($field, $expected) || $expected[$field] !== ($context[$field] ?? null)) {
                    throw new RuntimeException('Archive proof and frozen source snapshot disagree.');
                }
            }
            if (($context['city'] ?? null) !== $city || ($context['mapping_sha256'] ?? null) !== OperationalManifest::SHA256
                || ($context['schema_sha256'] ?? null) !== OperationalManifest::SHA256
                || ($context['table_counts'][$group['table']] ?? null) !== $group['rows_per_city']) {
                throw new RuntimeException('Frozen sharing source table is incomplete or uses another interpretation.');
            }
            $source = $this->db->table('sources')->where('id', $context['source_id'])->first();
            $storedCity = $source ? $this->db->table('cities')->where('id', $source->city_id)->first() : null;
            if (! $storedCity || $storedCity->name_en !== $city) {
                throw new RuntimeException('Sharing source is assigned to another city.');
            }
            $context['city_id'] = (int) $source->city_id;
            $context['source_id'] = (int) $source->id;
            $context['snapshot_id'] = (int) $context['snapshot_id'];
            $context['mapping'] = OperationalManifest::mapping($city, $group['table']);
            $this->checkSource($context);
            $contexts[$city] = $context;
        }

        return $contexts;
    }

    private function checkSource(array $context): void
    {
        $snapshot = $this->db->table('snapshots')->where(['id' => $context['snapshot_id'], 'source_id' => $context['source_id']])->first();
        if (! $snapshot || bin2hex($snapshot->archive_sha256) !== $context['archive_sha256'] || bin2hex($snapshot->sql_sha256) !== $context['sql_sha256']) {
            throw new RuntimeException('Sharing snapshot does not match the frozen source evidence.');
        }
        $contract = $this->db->table('migration_run_contracts')->where('snapshot_id', $context['snapshot_id'])
            ->where('mapping_sha256', hex2bin($context['mapping_sha256']))->first();
        if (! $contract || bin2hex($contract->frozen_manifest_sha256) !== $context['frozen_manifest_sha256']
            || bin2hex($contract->schema_sha256) !== $context['schema_sha256']) {
            throw new RuntimeException('Sharing source has no matching immutable imported interpretation.');
        }
    }

    private function verify(FrozenSourceReader $reader, string $table): void
    {
        $report = $this->reconciler->verifySharingTableInTransaction($reader, $table);
        if (($report['matches'] ?? null) !== true || ($report['source_anomalies'] ?? 0) !== 0) {
            throw new RuntimeException('Independent whole-table source, origin, payload or relationship verification failed.');
        }
    }

    private function parents(array $group, array $contexts, array $readers): void
    {
        foreach ($group['declared_parent_closure'] as $parent) {
            foreach ($contexts as $city => $context) {
                $mapping = OperationalManifest::mapping($city, $parent);
                $membership = $this->db->table('shared_catalog_memberships as m')->join('shared_catalog_groups as g', 'g.id', '=', 'm.group_id')
                    ->where('m.source_id', $context['source_id'])->where('m.snapshot_id', $context['snapshot_id'])
                    ->where('m.source_table_id', $mapping['source_table_id'])->where('g.status', 'published')->first(['g.*']);
                if (! $membership) {
                    throw new RuntimeException('Parent equality closure must be verified and published first.');
                }
                $valid = false;
                foreach (SharedCatalogManifest::groups($parent) as $candidate) {
                    if (in_array($city, $candidate['cities'], true) && hex2bin($candidate['full_table_sha256']) === $membership->full_table_sha256
                        && hex2bin($candidate['schema_contract_sha256']) === $membership->schema_contract_sha256
                        && $candidate['rows_per_city'] === (int) $membership->row_count) {
                        $valid = true;
                    }
                }
                if (! $valid) {
                    throw new RuntimeException('Parent membership is outside the approved equality closure.');
                }
                $this->verify($readers[$city], $parent);
            }
        }
    }

    private function rows(array $group, FrozenSourceReader $reader, array $context, array $columns): array
    {
        $rows = [];
        $mapping = $context['mapping'];
        $digests = [];
        foreach ($reader->rows($group['table']) as $row) {
            if (($row['ordinal'] ?? null) !== count($rows) + 1) {
                throw new RuntimeException('Sharing source occurrence order is not contiguous.');
            }
            $payload = [];
            foreach ($mapping['columns'] as $column) {
                $payload[$column['target_column']] = $row['values'][$column['source_column']];
            }
            $bytes = SharedRowCodec::encode($payload, $columns);
            $hash = hash('sha256', $bytes, true);
            if (isset($digests[$hash]) && $digests[$hash] !== $bytes) {
                throw new RuntimeException('Canonical row digest collision; sharing cannot continue.');
            }
            $digests[$hash] = $bytes;
            $record = $this->db->table('source_records')->where(['source_id' => $context['source_id'],
                'snapshot_id' => $context['snapshot_id'], 'table_id' => $mapping['source_table_id'],
                'key_sha256' => hash('sha256', $row['key'], true)])->first();
            if (! $record || $record->source_key !== $row['key']) {
                throw new RuntimeException('Sharing occurrence key has no exact preserved mapping.');
            }
            $origin = $this->db->table('operational_origins')->where(['source_record_id' => $record->id,
                'source_id' => $context['source_id'], 'source_table_id' => $mapping['source_table_id'], 'snapshot_id' => $context['snapshot_id']])->first();
            if (! $origin) {
                throw new RuntimeException('Sharing occurrence has no exact operational origin.');
            }
            $rows[] = ['payload' => $payload, 'bytes' => $bytes, 'hash' => $hash, 'entity_id' => (int) $origin->entity_id, 'key' => $row['key']];
        }
        if (count($rows) !== $group['rows_per_city']) {
            throw new RuntimeException('Sharing requires the entire approved table, including every occurrence.');
        }
        usort($rows, fn ($a, $b) => strcmp($a['bytes'], $b['bytes']) ?: strcmp($a['key'], $b['key']));

        return $rows;
    }

    private function canonical(array $table, int $group, int $tableId, array $rows, array $columns, bool $reused): array
    {
        if ($reused && $this->db->table($table['name'])->where('group_id', $group)->count() !== count($rows)) {
            throw new RuntimeException('Published canonical table has extra or missing rows.');
        }
        $result = [];
        $duplicates = [];
        foreach ($rows as $index => $row) {
            $duplicate = ($duplicates[$row['hash']] ?? 0) + 1;
            $duplicates[$row['hash']] = $duplicate;
            if ($reused) {
                $canonical = $this->db->table($table['name'])->where(['group_id' => $group, 'row_ordinal' => $index + 1])->first();
            } else {
                $id = $this->db->table($table['name'])->insertGetId(['group_id' => $group, 'source_table_id' => $tableId,
                    'row_ordinal' => $index + 1, 'row_sha256' => $row['hash'], 'duplicate_ordinal' => $duplicate] + $row['payload']);
                TargetWarnings::assertNone($this->db);
                $canonical = $this->db->table($table['name'])->where('id', $id)->first();
            }
            if (! $canonical || (int) $canonical->source_table_id !== $tableId || (int) $canonical->duplicate_ordinal !== $duplicate
                || $canonical->row_sha256 !== $row['hash'] || SharedRowCodec::encode((array) $canonical, $columns) !== $row['bytes']) {
                throw new RuntimeException('Canonical full bytes, digest or duplicate identity does not match the complete source table.');
            }
            $result[$index] = (int) $canonical->id;
        }

        return $result;
    }

    private function membership(int $group, int $table, int $count, array $context, string $evidence, bool $reused): void
    {
        $key = ['source_id' => $context['source_id'], 'source_table_id' => $table, 'snapshot_id' => $context['snapshot_id']];
        $existing = $this->db->table('shared_catalog_memberships')->where($key)->first();
        if ($reused) {
            if (! $existing || (int) $existing->group_id !== $group || (int) $existing->city_id !== $context['city_id'] || (int) $existing->row_count !== $count) {
                throw new RuntimeException('Published whole-table membership changed or is missing.');
            }
        } else {
            if ($existing) {
                throw new RuntimeException('Source table snapshot already belongs to another sharing group.');
            }
            $this->db->table('shared_catalog_memberships')->insert($key + ['group_id' => $group, 'city_id' => $context['city_id'],
                'row_count' => $count, 'verified_at' => now('UTC')->format('Y-m-d H:i:s.u'), 'evidence_locator' => $evidence]);
        }
    }

    private function complete(array $group, int $groupId, array $contexts, array $table): void
    {
        $memberships = $this->db->table('shared_catalog_memberships')->where('group_id', $groupId)->get();
        if ($memberships->count() !== count($contexts)
            || $this->db->table($table['name'])->where('group_id', $groupId)->count() !== $group['rows_per_city']) {
            throw new RuntimeException('Published equality group has incomplete or extra membership/canonical coverage.');
        }
        foreach ($contexts as $context) {
            $count = $this->db->table($context['mapping']['target_table'])->where(['source_id' => $context['source_id'],
                'shared_group_id' => $groupId, 'shared_snapshot_id' => $context['snapshot_id']])->count();
            if ($count !== $group['rows_per_city']) {
                throw new RuntimeException('Published equality group does not cover the entire scoped alias table.');
            }
        }
    }
}
