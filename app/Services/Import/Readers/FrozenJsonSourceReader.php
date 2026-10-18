<?php

declare(strict_types=1);

namespace App\Services\Import\Readers;

use App\Services\Import\FrozenSourceReader;
use App\Services\Import\KeyEncoder;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use Generator;
use RuntimeException;

require_once __DIR__.'/../../../../database/schema/OperationalManifest.php';

/** Reads a fully hashed immutable extraction. Payload bytes never enter JSON numbers. */
final class FrozenJsonSourceReader implements FrozenSourceReader
{
    private array $header;

    private array $mappings = [];

    private string $directory;

    private string $manifestHash;

    public function __construct(string $manifestPath)
    {
        $path = realpath($manifestPath);
        if ($path === false || ! is_file($path) || is_link($manifestPath)) {
            throw new RuntimeException('Frozen source manifest is not a regular local file.');
        }
        $this->directory = dirname($path);
        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new RuntimeException('Frozen source manifest cannot be read.');
        }
        $this->manifestHash = hash('sha256', $bytes);
        $this->header = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (($this->header['format'] ?? null) !== 'micro-projects-frozen-source-v1'
            || ($this->header['key_encoding_version'] ?? null) !== KeyEncoder::VERSION) {
            throw new RuntimeException('Unsupported frozen source format.');
        }
        $city = $this->header['city'] ?? '';
        if (! is_string($city) || PreservationManifest::sourceId($city) !== ($this->header['source_id'] ?? null)
            || ! is_int($this->header['snapshot_id'] ?? null) || $this->header['snapshot_id'] < 1) {
            throw new RuntimeException('Frozen source identity is invalid.');
        }
        foreach (['archive_sha256', 'sql_sha256', 'mapping_sha256', 'schema_sha256', 'extractor_sha256'] as $field) {
            if (! is_string($this->header[$field] ?? null) || ! preg_match('/\A[0-9a-f]{64}\z/D', $this->header[$field])) {
                throw new RuntimeException('Frozen source hash is invalid.');
            }
        }
        if ($this->header['mapping_sha256'] !== OperationalManifest::SHA256
            || $this->header['schema_sha256'] !== OperationalManifest::SHA256
            || ! is_string($this->header['extractor_version'] ?? null) || $this->header['extractor_version'] === '') {
            throw new RuntimeException('Frozen source does not use the approved mapping.');
        }
        foreach (OperationalManifest::load()['mappings'] as $mapping) {
            if ($mapping['city'] === $city) {
                $this->mappings[$mapping['source_table']] = $mapping;
            }
        }
        $names = array_keys($this->mappings);
        $files = array_keys($this->header['files'] ?? []);
        sort($names);
        sort($files);
        if ($names !== $files) {
            throw new RuntimeException('Frozen source must enumerate every city table, including empty tables.');
        }
        foreach ($this->header['files'] as $file) {
            if (! is_int($file['rows'] ?? null) || $file['rows'] < 0
                || ! is_string($file['sha256'] ?? null) || ! preg_match('/\A[0-9a-f]{64}\z/D', $file['sha256'])) {
                throw new RuntimeException('Frozen table metadata is invalid.');
            }
            $tablePath = $this->tablePath($file['file'] ?? '');
            if (hash_file('sha256', $tablePath) !== $file['sha256']) {
                throw new RuntimeException('Frozen table checksum mismatch.');
            }
        }
    }

    public function contract(): array
    {
        $contract = $this->header;
        unset($contract['files']);
        $contract['frozen_manifest_sha256'] = $this->manifestHash;
        $contract['table_counts'] = array_map(static fn (array $file): int => $file['rows'], $this->header['files']);

        return $contract;
    }

    public function tables(): array
    {
        $names = array_keys($this->mappings);
        sort($names);

        return $names;
    }

    public function rows(string $table): iterable
    {
        if (! isset($this->mappings[$table])) {
            throw new RuntimeException('Unknown frozen source table.');
        }

        return $this->readRows($table);
    }

    private function readRows(string $table): Generator
    {
        $file = $this->header['files'][$table];
        $handle = fopen($this->tablePath($file['file']), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Frozen table cannot be opened.');
        }
        $digest = hash_init('sha256');
        $ordinal = 0;
        $mapping = $this->mappings[$table];
        $columnDefinitions = array_column($mapping['columns'], 'source_definition', 'source_column');
        try {
            while (($line = fgets($handle)) !== false) {
                hash_update($digest, $line);
                $row = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                if (($row['ordinal'] ?? null) !== ++$ordinal || ! is_array($row['values'] ?? null)) {
                    throw new RuntimeException('Frozen row order or payload is invalid.');
                }
                $actual = array_keys($row['values']);
                $expected = array_keys($columnDefinitions);
                sort($actual);
                sort($expected);
                if ($actual !== $expected) {
                    throw new RuntimeException('Frozen row does not include exactly the source columns.');
                }
                $values = [];
                foreach ($row['values'] as $name => $encoded) {
                    if ($encoded === null) {
                        $values[$name] = null;
                    } elseif (is_string($encoded) && ($decoded = base64_decode($encoded, true)) !== false
                        && base64_encode($decoded) === $encoded) {
                        $values[$name] = $decoded;
                    } else {
                        throw new RuntimeException('Frozen value is not canonical base64 or SQL NULL.');
                    }
                }
                $primary = $mapping['legacy_primary_key'];
                $key = $primary === [] ? KeyEncoder::ordinal($this->header['sql_sha256'], $table, $ordinal) : '';
                // Preserve the original PK component order rather than source-column declaration order.
                if ($primary !== []) {
                    $ordered = [];
                    foreach ($primary as $name) {
                        $ordered[$name] = $columnDefinitions[$name];
                    }
                    $key = KeyEncoder::primary($ordered, $values);
                }
                yield ['ordinal' => $ordinal, 'cursor' => (string) $ordinal, 'key' => $key,
                    'key_kind' => $primary === [] ? 'ordinal' : 'primary', 'values' => $values];
            }
            if (! feof($handle) || $ordinal !== $file['rows'] || hash_final($digest) !== $file['sha256']) {
                throw new RuntimeException('Frozen table changed or has an incorrect occurrence count.');
            }
        } finally {
            fclose($handle);
        }
    }

    private function tablePath(string $name): string
    {
        if ($name === '' || str_contains($name, '..') || ! preg_match('/\A[a-zA-Z0-9_.-]+\.jsonl\z/D', $name)) {
            throw new RuntimeException('Unsafe frozen table filename.');
        }
        $path = $this->directory.DIRECTORY_SEPARATOR.$name;
        $resolved = realpath($path);
        if ($resolved === false || ! is_file($resolved) || is_link($path)
            || strcasecmp(dirname($resolved), $this->directory) !== 0) {
            throw new RuntimeException('Frozen table must be a regular file beside its manifest.');
        }

        return $resolved;
    }
}
