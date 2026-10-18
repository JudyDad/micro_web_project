<?php

declare(strict_types=1);

namespace App\Services\Import\Readers;

use App\Services\Import\KeyEncoder;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use PDO;
use RuntimeException;
use Throwable;

require_once __DIR__.'/../../../../database/schema/OperationalManifest.php';

/** Freezes a read-only, isolated MySQL snapshot into exact-byte, hashed row streams. */
final class NativePdoSourceExporter
{
    public const VERSION = 'native-pdo-frozen-v1';

    public function export(PDO $source, array $identity, string $outputDirectory): string
    {
        if ($source->inTransaction() || is_dir($outputDirectory) || file_exists($outputDirectory)) {
            throw new RuntimeException('Use a new extraction directory and an idle isolated source connection.');
        }
        $parent = realpath(dirname($outputDirectory));
        if ($parent === false || ! is_dir($parent) || in_array(basename($outputDirectory), ['', '.', '..'], true)) {
            throw new RuntimeException('Use a new local directory inside an existing private parent folder.');
        }
        $outputDirectory = $parent.DIRECTORY_SEPARATOR.basename($outputDirectory);
        $normalized = strtolower(str_replace('\\', '/', $outputDirectory)).'/';
        $repository = strtolower(str_replace('\\', '/', dirname(__DIR__, 4))).'/';
        if (str_starts_with($normalized, $repository) && ! str_starts_with($normalized, $repository.'storage/app/private/')) {
            throw new RuntimeException('Frozen business rows must stay outside tracked/public application directories.');
        }
        $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $source->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $source->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $database = $source->query('SELECT DATABASE()')->fetchColumn();
        $version = $source->query('SELECT VERSION()')->fetchColumn();
        if (! is_string($database) || ! preg_match('/\Amicro_projects_source_[a-z0-9_]+\z/D', $database)
            || ! is_string($version) || ! str_starts_with($version, '8.') || str_contains($version, 'MariaDB')) {
            throw new RuntimeException('Export only a separately restored micro_projects_source_* database on MySQL 8.');
        }
        foreach (['archive_path' => 'archive_sha256', 'sql_path' => 'sql_sha256'] as $path => $hash) {
            if (! is_string($identity[$path] ?? null) || ! is_file($identity[$path])
                || ! preg_match('/\A[0-9a-f]{64}\z/D', $identity[$hash] ?? '')
                || hash_file('sha256', $identity[$path]) !== $identity[$hash]) {
                throw new RuntimeException('Original archive or SQL member checksum mismatch.');
            }
        }
        $city = $identity['city'] ?? '';
        $sourceId = PreservationManifest::sourceId($city);
        if (($identity['source_id'] ?? null) !== $sourceId || ! is_int($identity['snapshot_id'] ?? null)
            || $identity['snapshot_id'] < 1) {
            throw new RuntimeException('Export source identity is invalid.');
        }
        $mappings = array_values(array_filter(OperationalManifest::load()['mappings'], static fn (array $m): bool => $m['city'] === $city));
        $expected = array_column($mappings, 'source_table');
        $observed = $source->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE=\'BASE TABLE\'')->fetchAll(PDO::FETCH_COLUMN);
        sort($expected);
        sort($observed);
        if ($observed !== $expected) {
            throw new RuntimeException('Restored source tables do not match the approved city schema.');
        }
        $this->verifySchema($source, $city);
        $encoding = $source->query('SELECT @@character_set_connection AS connection_charset, @@character_set_results AS results_charset')->fetch(PDO::FETCH_ASSOC);
        if ($encoding['connection_charset'] !== 'utf8mb4' || $encoding['results_charset'] !== 'utf8mb4') {
            throw new RuntimeException('Source extraction requires the reviewed utf8mb4 client encoding.');
        }
        $this->verifyNativeNumbers($source);
        if (! mkdir($outputDirectory, 0700, true)) {
            throw new RuntimeException('Cannot create the private extraction directory.');
        }
        $header = [
            'format' => 'micro-projects-frozen-source-v1', 'city' => $city, 'source_id' => $sourceId,
            'snapshot_id' => $identity['snapshot_id'], 'archive_sha256' => $identity['archive_sha256'],
            'sql_sha256' => $identity['sql_sha256'], 'mapping_sha256' => OperationalManifest::SHA256,
            'schema_sha256' => OperationalManifest::SHA256, 'extractor_sha256' => hash_file('sha256', __FILE__),
            'extractor_version' => self::VERSION, 'key_encoding_version' => KeyEncoder::VERSION,
            'source_server' => $version, 'source_database' => $database,
            'source_session' => $source->query('SELECT @@session.sql_mode AS sql_mode, @@session.time_zone AS time_zone, @@character_set_connection AS charset')->fetch(PDO::FETCH_ASSOC),
            'keyless_order' => 'frozen-extraction-stream; stable only within this hashed extract, never unordered database reruns',
            'files' => [],
        ];
        $source->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        $source->exec('SET TRANSACTION READ ONLY');
        $source->beginTransaction();
        try {
            foreach ($mappings as $mapping) {
                $table = $mapping['source_table'];
                $file = 'table-'.$mapping['source_table_id'].'.jsonl';
                $handle = fopen($outputDirectory.DIRECTORY_SEPARATOR.$file, 'xb');
                if ($handle === false) {
                    throw new RuntimeException('Cannot create frozen table stream.');
                }
                $definitions = array_column($mapping['columns'], 'source_definition', 'source_column');
                $sql = 'SELECT '.implode(',', array_map(PreservationManifest::quote(...), array_keys($definitions)))
                    .' FROM '.PreservationManifest::quote($table);
                if ($mapping['legacy_primary_key'] !== []) {
                    $sql .= ' ORDER BY '.implode(',', array_map(PreservationManifest::quote(...), $mapping['legacy_primary_key']));
                }
                $source->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                $statement = $source->prepare($sql);
                $statement->execute();
                $count = 0;
                $digest = hash_init('sha256');
                try {
                    while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                        $values = [];
                        foreach ($definitions as $column => $definition) {
                            $value = $row[$column];
                            if ($value !== null && preg_match('/\A(?:float|double)\b/i', $definition)) {
                                if (! is_float($value) && ! is_int($value)) {
                                    throw new RuntimeException('Source driver did not return native floating-point values.');
                                }
                                // Native prepared binary result -> roundtrip decimal; never JSON floating numbers.
                                $value = sprintf('%.17g', $value);
                                $value = str_replace(',', '.', $value);
                            } elseif ($value !== null && ! is_string($value) && ! is_int($value)) {
                                throw new RuntimeException('Unexpected native source value type.');
                            }
                            $values[$column] = $value === null ? null : base64_encode((string) $value);
                        }
                        $line = json_encode(['ordinal' => ++$count, 'values' => $values], JSON_THROW_ON_ERROR)."\n";
                        if (fwrite($handle, $line) !== strlen($line)) {
                            throw new RuntimeException('Incomplete frozen table write.');
                        }
                        hash_update($digest, $line);
                    }
                } finally {
                    $statement->closeCursor();
                    fclose($handle);
                }
                $source->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                $header['files'][$table] = ['file' => $file, 'rows' => $count, 'sha256' => hash_final($digest)];
            }
            $source->commit();
            $manifestPath = $outputDirectory.DIRECTORY_SEPARATOR.'manifest.json';
            $manifestBytes = json_encode($header, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
            if (file_put_contents($manifestPath, $manifestBytes, LOCK_EX) !== strlen($manifestBytes)) {
                throw new RuntimeException('Incomplete frozen source manifest write.');
            }
            // Re-open every hashed stream before returning an artifact usable by the importer.
            new FrozenJsonSourceReader($manifestPath);

            return $manifestPath;
        } catch (Throwable $error) {
            if ($source->inTransaction()) {
                $source->rollBack();
            }
            // Keep incomplete artifacts private for diagnosis; no manifest authorizes consuming them.
            throw new RuntimeException('Source extraction failed; private incomplete output retained.', 0, $error);
        }
    }

    private function verifyNativeNumbers(PDO $source): void
    {
        $statement = $source->prepare('SELECT CAST(0.10000000149011612 AS FLOAT) AS f, CAST(1.0000000000000002 AS DOUBLE) AS d');
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        if (! is_float($row['f']) || ! is_float($row['d'])
            || pack('G', $row['f']) !== hex2bin('3dcccccd') || pack('E', $row['d']) !== hex2bin('3ff0000000000001')) {
            throw new RuntimeException('Native binary FLOAT/DOUBLE extraction vectors failed.');
        }
    }

    private function verifySchema(PDO $source, string $city): void
    {
        $statement = $source->query('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION');
        $actual = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $actual[$column['TABLE_NAME']][$column['COLUMN_NAME']] = $column;
        }
        $primary = [];
        foreach ($source->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND INDEX_NAME='PRIMARY' ORDER BY TABLE_NAME,SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $primary[$column['TABLE_NAME']][] = $column['COLUMN_NAME'];
        }
        $normalize = static fn (string $type): string => preg_replace('/\b(tinyint|smallint|mediumint|int|bigint)\(\d+\)/i', '$1', strtolower($type));
        foreach (PreservationManifest::load()['tables'] as $table) {
            if ($table['city'] !== $city) {
                continue;
            }
            $name = $table['source_table'];
            if (array_keys($actual[$name] ?? []) !== array_column($table['columns'], 'source_column')
                || ($primary[$name] ?? []) !== $table['legacy_primary_key']) {
                throw new RuntimeException('Restored source column or primary-key contract differs.');
            }
            preg_match('/DEFAULT CHARSET=(\w+)(?: COLLATE=(\w+))?/', $table['original_ddl'], $defaults);
            $defaultCollation = $defaults[2] ?? (($defaults[1] ?? '') === 'utf8mb3' ? 'utf8mb3_general_ci' : 'utf8mb4_0900_ai_ci');
            foreach ($table['columns'] as $column) {
                $definition = $column['source_definition'];
                $found = $actual[$name][$column['source_column']];
                preg_match('/^([a-z]+(?:\([^)]*\))?(?: unsigned)?)/i', $definition, $type);
                if ($normalize($type[1] ?? '') !== $normalize($found['COLUMN_TYPE'])
                    || (str_contains($definition, 'NOT NULL') ? 'NO' : 'YES') !== $found['IS_NULLABLE']) {
                    throw new RuntimeException('Restored source type or nullability contract differs.');
                }
                if ($found['COLLATION_NAME'] !== null) {
                    preg_match('/COLLATE\s+(\w+)/', $definition, $collation);
                    if (($collation[1] ?? $defaultCollation) !== $found['COLLATION_NAME']) {
                        throw new RuntimeException('Restored source text collation differs.');
                    }
                }
            }
        }
    }
}
