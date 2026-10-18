<?php

declare(strict_types=1);

namespace App\Services\AttachmentReconciliation;

use App\Services\Import\FrozenSourceReader;
use App\Services\Reconciliation\SourceKeyInspector;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use RuntimeException;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

final class ReferenceInventory
{
    public const FORMAT = 'micro-projects-attachment-evidence-v1';

    /** Metadata-only coverage, including zero-row fields. */
    public static function fields(): array
    {
        $fields = [];
        foreach (OperationalManifest::load()['mappings'] as $mapping) {
            foreach ($mapping['columns'] as $column) {
                $blob = preg_match('/\b(?:tiny|medium|long)?blob\b/i', $column['source_definition']) === 1;
                $external = in_array($mapping['source_table'].'.'.$column['source_column'], ['image.image_NewPath', 'manualfile.Path'], true);
                if ($blob || $external) {
                    $fields[] = ['city' => $mapping['city'], 'source_table' => $mapping['source_table'], 'source_column' => $column['source_column'],
                        'source_definition' => $column['source_definition'], 'kind' => $blob ? 'blob' : 'external'];
                }
            }
        }
        if (count($fields) !== 30) {
            throw new RuntimeException('Attachment field coverage changed from the approved schema.');
        }

        return $fields;
    }

    /** No database, storage server, owner grant or publication is accessed. */
    public function write(array $readers, string $newDirectory): array
    {
        if ($readers === []) {
            throw new RuntimeException('At least one complete frozen source is required.');
        }
        $sources = [];
        foreach ($readers as $reader) {
            if (! $reader instanceof FrozenSourceReader) {
                throw new RuntimeException('Attachment inventory requires frozen source readers.');
            }
            $context = $reader->contract();
            if (isset($sources[$context['city']]) || PreservationManifest::sourceId($context['city']) !== $context['source_id']
                || $context['mapping_sha256'] !== OperationalManifest::SHA256 || $context['schema_sha256'] !== OperationalManifest::SHA256) {
                throw new RuntimeException('Attachment inventory source scope is invalid or duplicated.');
            }
            foreach (['archive_sha256', 'sql_sha256', 'frozen_manifest_sha256'] as $hash) {
                if (! preg_match('/\A[0-9a-f]{64}\z/D', $context[$hash] ?? '')) {
                    throw new RuntimeException('Attachment inventory needs exact frozen source hashes.');
                }
            }
            $sources[$context['city']] = array_intersect_key($context, array_flip(['city', 'source_id', 'snapshot_id', 'archive_sha256', 'sql_sha256', 'frozen_manifest_sha256']));
        }
        $directory = PrivateEvidenceDirectory::create($newDirectory);
        $path = $directory.DIRECTORY_SEPARATOR.'references.jsonl';
        $stream = @fopen($path, 'xb');
        if ($stream === false) {
            throw new RuntimeException('Private reference inventory cannot be created.');
        }
        if (PHP_OS_FAMILY !== 'Windows' && ! chmod($path, 0600)) {
            fclose($stream);
            throw new RuntimeException('Private reference permissions cannot be restricted.');
        }
        $count = $nullBlobs = $emptyBlobs = 0;
        $seen = [];
        $coverage = [];
        $allFields = self::fields();
        try {
            foreach ($readers as $reader) {
                $context = $reader->contract();
                foreach (OperationalManifest::load()['mappings'] as $mapping) {
                    if ($mapping['city'] !== $context['city']) {
                        continue;
                    }
                    $fields = array_values(array_filter($allFields, fn (array $field): bool => $field['city'] === $context['city'] && $field['source_table'] === $mapping['source_table']));
                    if ($fields === []) {
                        continue;
                    }
                    foreach ($fields as $field) {
                        $coverage[] = $field;
                    }
                    $ordinal = 0;
                    foreach ($reader->rows($mapping['source_table']) as $row) {
                        if (($row['ordinal'] ?? null) !== ++$ordinal) {
                            throw new RuntimeException('Attachment source row order is not contiguous.');
                        }
                        $definitions = [];
                        foreach ($mapping['legacy_primary_key'] as $key) {
                            $definitions[$key] = array_column($mapping['columns'], 'source_definition', 'source_column')[$key];
                        }
                        if ($definitions === [] || ! (new SourceKeyInspector)->primaryMatches($row['key'], $definitions, $row['values'])) {
                            throw new RuntimeException('Attachment source identity does not match original fields.');
                        }
                        foreach ($fields as $field) {
                            $reference = $this->reference($context, $mapping, $field, $row);
                            if (isset($seen[$reference['id']])) {
                                throw new RuntimeException('Attachment reference identity collision or duplicate occurrence.');
                            }
                            $seen[$reference['id']] = true;
                            $count++;
                            $nullBlobs += $reference['kind'] === 'blob' && $reference['blob_null'] ? 1 : 0;
                            $emptyBlobs += $reference['kind'] === 'blob' && $reference['expected'] !== null && $reference['expected']['length'] === '0' ? 1 : 0;
                            PrivateEvidenceDirectory::append($stream, json_encode($reference, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n");
                        }
                    }
                    if ($ordinal !== ($context['table_counts'][$mapping['source_table']] ?? null)) {
                        throw new RuntimeException('Attachment source row count differs from the frozen manifest.');
                    }
                }
            }
            if (! fflush($stream) || (function_exists('fsync') && ! fsync($stream))) {
                throw new RuntimeException('Private reference evidence could not be flushed.');
            }
        } finally {
            fclose($stream);
        }
        $manifest = ['format' => self::FORMAT, 'schema_sha256' => OperationalManifest::SHA256, 'references_file' => 'references.jsonl',
            'references_sha256' => hash_file('sha256', $path), 'reference_count' => $count, 'sources' => array_values($sources), 'field_coverage' => $coverage,
            'expected_global_field_instances' => 30, 'complete_five_city_inventory' => count($sources) === 5 && count($coverage) === 30,
            'created_at_utc' => gmdate('Y-m-d\TH:i:s\Z'), 'publication' => 'none; separate owner and capability review required'];
        PrivateEvidenceDirectory::write($directory, 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n");

        return ['manifest_sha256' => hash_file('sha256', $directory.DIRECTORY_SEPARATOR.'manifest.json'), 'reference_count' => $count,
            'field_instances' => count($coverage), 'complete_five_city_inventory' => $manifest['complete_five_city_inventory'],
            'null_blobs' => $nullBlobs, 'empty_blobs' => $emptyBlobs, 'published' => false];
    }

    private function reference(array $context, array $mapping, array $field, array $row): array
    {
        $value = $row['values'][$field['source_column']];
        if ($value !== null && ! is_string($value)) {
            throw new RuntimeException('Attachment source payload must use exact bytes or NULL.');
        }
        $identity = ['source_id' => $context['source_id'], 'snapshot_id' => $context['snapshot_id'], 'sql_sha256' => $context['sql_sha256'],
            'table' => $mapping['source_table'], 'source_key_b64' => base64_encode($row['key']), 'column' => $field['source_column'], 'kind' => $field['kind'], 'occurrence' => 1];
        $owner = match ($mapping['source_table']) {
            'file' => ['project' => $row['values']['MicroProject_ID']],
            'image' => ['project' => $row['values']['MicroProject_ID'], 'person' => $row['values']['Person_ID']],
            'user' => ['user' => $row['values']['UserID']],
            default => [],
        };
        preg_match('/(?:CHARACTER SET\s+(\w+)|COLLATE\s+(\w+))/', $field['source_definition'], $charset);
        $encoding = ! empty($charset[1]) ? $charset[1] : (! empty($charset[2]) ? explode('_', $charset[2])[0] : null);
        $blob = $field['kind'] === 'blob';

        return ['version' => 1, 'id' => hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)), 'identity' => $identity,
            'city' => $context['city'], 'source_id' => $context['source_id'], 'snapshot_id' => $context['snapshot_id'], 'table' => $mapping['source_table'],
            'column' => $field['source_column'], 'kind' => $field['kind'], 'source_ordinal' => $row['ordinal'],
            'owner_original' => ['source_id' => $context['source_id'], 'snapshot_id' => $context['snapshot_id'], 'keys' => $owner, 'resolution' => 'unreviewed'],
            'mapped_owner_ids' => [], 'download_policy' => 'denied_pending_review',
            'raw_reference_is_null' => $blob ? null : $value === null, 'raw_reference_b64' => $blob || $value === null ? null : base64_encode($value),
            'reference_charset' => $blob ? null : $encoding, 'blob_null' => $blob ? $value === null : null,
            'source_blob_type' => $blob ? $field['source_definition'] : null,
            'expected' => $blob && $value !== null ? ['length' => (string) strlen($value), 'sha256' => hash('sha256', $value), 'anchor' => $context['frozen_manifest_sha256']] : null,
            'display_name_b64' => $mapping['source_table'] === 'file' && is_string($row['values']['File_Name'] ?? null)
                ? base64_encode($row['values']['File_Name']) : null,
            'read_status' => $blob && $value === null ? 'null_blob' : 'not_checked'];
    }
}
