<?php

declare(strict_types=1);

namespace App\Services\SharingEvidence;

use App\Services\Import\FrozenSourceReader;
use App\Services\Reconciliation\CellComparator;
use App\Services\Sharing\CatalogSourceProof;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use Database\Schema\SharedCatalogManifest;
use RuntimeException;
use ZipArchive;

require_once __DIR__.'/../../../database/schema/SharedCatalogManifest.php';

/** Independent immutable-SQL proof; no SQL is executed and no private value is logged. */
final class ArchiveCatalogProof implements CatalogSourceProof
{
    private const TOKEN = <<<'REGEX'
~\G\s*(?:(?<string>'(?:\\.|''|[^'\\])*')|(?<hex>0x[0-9a-fA-F]+)|(?<null>NULL\b)|(?<number>[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?)|(?<punct>[(),;]))~s
REGEX;

    private array $cache = [];

    public function __construct(private readonly array $archivePathsByCity) {}

    public function verify(array $group, array $readersByCity): array
    {
        if ($group !== SharedCatalogManifest::approvedGroup($group['table'], $group['cities'])) {
            throw new RuntimeException('Sharing proof requires an exact approved whole-table group.');
        }
        $sources = [];
        $comparator = new CellComparator;
        foreach ($group['cities'] as $city) {
            $reader = $readersByCity[$city] ?? null;
            if (! $reader instanceof FrozenSourceReader) {
                throw new RuntimeException('Sharing proof is missing a complete city source.');
            }
            $contract = $reader->contract();
            $baseline = SharedCatalogManifest::sourceBaseline($city);
            if (($contract['city'] ?? null) !== $city || ($contract['source_id'] ?? null) !== PreservationManifest::sourceId($city)
                || ($contract['archive_sha256'] ?? null) !== $baseline['archive_sha256']
                || ($contract['sql_sha256'] ?? null) !== $baseline['sql_sha256']
                || ($contract['mapping_sha256'] ?? null) !== OperationalManifest::SHA256
                || ($contract['schema_sha256'] ?? null) !== OperationalManifest::SHA256
                || ! is_int($contract['snapshot_id'] ?? null) || $contract['snapshot_id'] < 1
                || ! preg_match('/\A[0-9a-f]{64}\z/D', $contract['frozen_manifest_sha256'] ?? '')) {
                throw new RuntimeException('Sharing source does not match the approved immutable archive baseline.');
            }
            $tables = $this->archive($city);
            $table = $tables[$group['table']];
            if ($table['schema_hash'] !== $group['schema_contract_sha256']
                || $table['table_hash'] !== $group['full_table_sha256'] || count($table['rows']) !== $group['rows_per_city']
                || ($contract['table_counts'][$group['table']] ?? null) !== $group['rows_per_city']) {
                throw new RuntimeException('Whole-table archive fingerprint or cardinality differs.');
            }
            $seen = [];
            $ordinal = 0;
            foreach ($reader->rows($group['table']) as $row) {
                if (($row['ordinal'] ?? null) !== ++$ordinal || ! is_array($row['values'] ?? null)) {
                    throw new RuntimeException('Sharing source occurrences are not contiguous.');
                }
                $key = $this->key($table['metadata'], $row['values']);
                if (isset($seen[$key]) || ! isset($table['rows'][$key])) {
                    throw new RuntimeException('Sharing source has a duplicate or unexpected original ID.');
                }
                $seen[$key] = true;
                $expected = $table['rows'][$key];
                if (count($row['values']) !== count($table['metadata']['columns'])) {
                    throw new RuntimeException('Sharing source columns differ from the archive.');
                }
                foreach ($table['metadata']['columns'] as $column) {
                    $name = $column['source_column'];
                    if (! array_key_exists($name, $row['values'])
                        || ! $comparator->equal($expected[$name], $row['values'][$name], $column['source_definition'])) {
                        throw new RuntimeException('Sharing source cell differs from its original SQL archive.');
                    }
                }
            }
            if (count($seen) !== count($table['rows'])) {
                throw new RuntimeException('Sharing source omitted an original archive occurrence.');
            }
            $sources[$city] = array_intersect_key($contract, array_flip(['archive_sha256', 'sql_sha256', 'frozen_manifest_sha256', 'snapshot_id']));
        }

        return ['verified' => true, 'full_table_sha256' => $group['full_table_sha256'],
            'schema_contract_sha256' => $group['schema_contract_sha256'], 'sources' => $sources];
    }

    /** Cache only candidate dictionaries; large business tables are skipped without decoding. */
    private function archive(string $city): array
    {
        if (isset($this->cache[$city])) {
            return $this->cache[$city];
        }
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Original archive proof requires the PHP zip extension.');
        }
        $path = $this->archivePathsByCity[$city] ?? null;
        $baseline = SharedCatalogManifest::sourceBaseline($city);
        if (! is_string($path) || realpath($path) === false || ! is_file($path)
            || hash_file('sha256', $path) !== $baseline['archive_sha256']) {
            throw new RuntimeException('Original sharing archive is missing or has changed.');
        }
        $tables = [];
        foreach (SharedCatalogManifest::load()['canonical_tables'] as $definition) {
            $name = substr($definition['name'], strlen('shared_'));
            $metadata = SharedCatalogManifest::sourceTable($city, $name);
            $tables[$name] = ['metadata' => $metadata, 'rows' => [], 'digests' => [],
                'schema_hash' => $this->schemaHash($metadata)];
        }
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('Original sharing archive cannot be opened read-only.');
        }
        $stream = null;
        try {
            $members = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                if (is_string($name) && str_ends_with($name, '.sql')) {
                    $members[] = $name;
                }
            }
            if (count($members) !== 1 || ($stream = $zip->getStream($members[0])) === false) {
                throw new RuntimeException('Sharing archive requires exactly one immutable SQL member.');
            }
            $digest = hash_init('sha256');
            while (($line = fgets($stream)) !== false) {
                hash_update($digest, $line);
                if (preg_match('/\AINSERT INTO `([^`]+)`\s*(\([^;]*?\))?\s*VALUES\s*/s', $line, $header)
                    && isset($tables[$header[1]])) {
                    $columns = ! empty($header[2]) ? $this->identifiers($header[2])
                        : array_column($tables[$header[1]]['metadata']['columns'], 'source_column');
                    $this->insert($tables[$header[1]], $columns, $line, strlen($header[0]));
                }
            }
            if (! feof($stream) || hash_final($digest) !== $baseline['sql_sha256']) {
                throw new RuntimeException('Original sharing SQL member checksum differs.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            $zip->close();
        }
        foreach ($tables as &$table) {
            ksort($table['digests'], SORT_STRING);
            $parts = [];
            foreach ($table['digests'] as $digest => $count) {
                $parts[] = hex2bin($digest).$this->unsigned64($count);
            }
            $table['table_hash'] = hash('sha256', hex2bin($table['schema_hash']).$this->frame($parts));
            unset($table['digests']);
        }
        unset($table);

        return $this->cache[$city] = $tables;
    }

    private function insert(array &$table, array $columns, string $line, int $offset): void
    {
        $expected = array_column($table['metadata']['columns'], 'source_column');
        $actual = $columns;
        sort($expected);
        sort($actual);
        if ($actual !== $expected || count($actual) !== count(array_unique($actual))) {
            throw new RuntimeException('Archive INSERT columns differ from the approved source table.');
        }
        $state = 'tuple';
        $values = $encoded = [];
        while (preg_match(self::TOKEN, $line, $token, PREG_UNMATCHED_AS_NULL, $offset)) {
            $offset += strlen($token[0]);
            $punct = $token['punct'];
            if ($state === 'tuple' && $punct === '(') {
                $state = 'value';
                $values = $encoded = [];
            } elseif ($state === 'value' && $punct === null) {
                [$value, $representation] = $this->literal($token);
                $values[] = $value;
                $encoded[] = $representation;
                $state = 'separator';
            } elseif ($state === 'separator' && $punct === ',') {
                $state = 'value';
            } elseif ($state === 'separator' && $punct === ')') {
                if (count($values) !== count($columns)) {
                    throw new RuntimeException('Archive tuple has an unexpected column count.');
                }
                $row = array_combine($columns, $values);
                $ordered = array_combine($columns, $encoded);
                $wire = [];
                foreach ($table['metadata']['columns'] as $column) {
                    $wire[] = $ordered[$column['source_column']];
                }
                $key = $this->key($table['metadata'], $row);
                if (isset($table['rows'][$key])) {
                    throw new RuntimeException('Original sharing table contains duplicate primary identities.');
                }
                $table['rows'][$key] = $row;
                $hash = hash('sha256', $this->frame($wire));
                $table['digests'][$hash] = ($table['digests'][$hash] ?? 0) + 1;
                $state = 'between';
            } elseif ($state === 'between' && $punct === ',') {
                $state = 'tuple';
            } elseif ($state === 'between' && $punct === ';' && trim(substr($line, $offset)) === '') {
                return;
            } else {
                throw new RuntimeException('Unsupported original sharing SQL tuple syntax.');
            }
        }
        throw new RuntimeException('Incomplete original sharing SQL statement.');
    }

    private function literal(array $token): array
    {
        if ($token['null'] !== null) {
            return [null, 'Z'];
        }
        if ($token['number'] !== null) {
            return [$token['number'], 'N'.$token['number']];
        }
        if ($token['hex'] !== null) {
            $bytes = hex2bin(substr($token['hex'], 2));
            if ($bytes === false) {
                throw new RuntimeException('Invalid original sharing SQL hex literal.');
            }

            return [$bytes, 'H'.$bytes];
        }
        $bytes = preg_replace_callback(<<<'REGEX'
~\\(.)|''~s
REGEX, static function (array $match): string {
            if ($match[0] === "''") {
                return "'";
            }
            $escapes = ['0' => "\0", 'b' => "\x08", 'n' => "\n", 'r' => "\r", 't' => "\t", 'Z' => "\x1a", '%' => '\\%', '_' => '\\_'];

            return $escapes[$match[1]] ?? $match[1];
        }, substr($token['string'], 1, -1));

        return [$bytes, 'S'.$bytes];
    }

    private function key(array $metadata, array $values): string
    {
        if ($metadata['legacy_primary_key'] === []) {
            throw new RuntimeException('Keyless archive tables are outside the approved canonical sharing scope.');
        }
        $parts = [];
        $definitions = array_column($metadata['columns'], 'source_definition', 'source_column');
        foreach ($metadata['legacy_primary_key'] as $column) {
            $value = $values[$column] ?? null;
            if (! is_string($value)) {
                throw new RuntimeException('Sharing original ID is missing.');
            }
            if (preg_match('/\A(?:tinyint|smallint|mediumint|int|bigint)\b/i', $definitions[$column])) {
                if (! preg_match('/\A[+-]?[0-9]+\z/D', $value)) {
                    throw new RuntimeException('Unsupported original integer ID literal.');
                }
                $digits = ltrim(ltrim($value, '+-'), '0');
                $value = ($digits !== '' && str_starts_with($value, '-') ? '-' : '').($digits === '' ? '0' : $digits);
            }
            $parts[] = $column;
            $parts[] = $value;
        }

        return $this->frame($parts);
    }

    private function schemaHash(array $metadata): string
    {
        $contract = [];
        foreach ($metadata['columns'] as $column) {
            $definition = $column['source_definition'];
            $type = preg_split('/\s+(?:NOT NULL|DEFAULT|AUTO_INCREMENT)\b/', $definition, 2)[0];
            $type = preg_replace('/\s+NULL$/', '', $type);
            if (preg_match('/\A(?:(?:var)?char\(|(?:tiny|medium|long)?text\b|enum\()/', $type)) {
                preg_match('/COLLATE\s+(\w+)/', $type, $collation);
                preg_match('/CHARACTER SET\s+(\w+)/', $type, $charset);
                preg_match('/DEFAULT CHARSET=(\w+)/', $metadata['original_ddl'], $defaultCharset);
                $encoding = $charset[1] ?? (isset($collation[1]) ? explode('_', $collation[1])[0] : $defaultCharset[1]);
                if ($charset === []) {
                    $type = $collation !== [] ? preg_replace('/\s+COLLATE/', ' CHARACTER SET '.$encoding.' COLLATE', $type) : $type.' CHARACTER SET '.$encoding;
                }
                if ($collation === []) {
                    preg_match('/COLLATE=(\w+)/', $metadata['original_ddl'], $defaultCollation);
                    $type .= ' COLLATE '.($defaultCollation[1] ?? $encoding.'_general_ci');
                }
            }
            $contract[] = [$column['source_column'], $type, str_contains($definition, 'NOT NULL')];
        }

        return hash('sha256', $this->pythonArrayJson($contract));
    }

    private function pythonArrayJson(mixed $value): string
    {
        return is_array($value) ? '['.implode(', ', array_map($this->pythonArrayJson(...), $value)).']'
            : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function identifiers(string $header): array
    {
        preg_match_all('/`([^`]+)`/', $header, $matches);

        return $matches[1];
    }

    private function frame(array $values): string
    {
        $bytes = '';
        foreach ($values as $value) {
            $bytes .= $this->unsigned64(strlen($value)).$value;
        }

        return $bytes;
    }

    private function unsigned64(int $value): string
    {
        return pack('N2', intdiv($value, 4294967296), $value & 0xFFFFFFFF);
    }
}
