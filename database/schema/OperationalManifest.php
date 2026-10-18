<?php

declare(strict_types=1);

namespace Database\Schema;

use RuntimeException;

require_once __DIR__.'/PreservationManifest.php';

final class OperationalManifest
{
    public const SHA256 = '24c6d75ce52f3361545174dc1f605f4888feb8360cc9aad67f460d68097b7254';

    private static ?array $manifest = null;

    public static function load(): array
    {
        // This frozen manifest is process-local immutable configuration. Validate
        // once before caching; PHP arrays return by value with copy-on-write.
        if (self::$manifest !== null) {
            return self::$manifest;
        }
        $path = __DIR__.'/operational-v1.json';
        if (! is_file($path) || hash_file('sha256', $path) !== self::SHA256) {
            throw new RuntimeException('Approved operational manifest changed; use a new reviewed migration.');
        }
        PreservationManifest::load();
        $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if ($manifest['source_manifest_sha256'] !== PreservationManifest::SHA256
            || count($manifest['tables']) !== 118 || count($manifest['support_tables']) !== 4) {
            throw new RuntimeException('Operational manifest preservation contract mismatch.');
        }

        self::$manifest = $manifest;

        return self::$manifest;
    }

    /** All approved domain and support table definitions. */
    public static function tables(): array
    {
        $manifest = self::load();

        return array_merge($manifest['tables'], $manifest['support_tables']);
    }

    public static function mapping(string $city, string $sourceTable): array
    {
        foreach (self::load()['mappings'] as $mapping) {
            if ($mapping['city'] === $city && $mapping['source_table'] === $sourceTable) {
                return $mapping;
            }
        }

        throw new RuntimeException('Unknown operational source mapping.');
    }

    /** Domain table by original source-table name; archive-only names fail closed. */
    public static function table(string $sourceTable): array
    {
        foreach (self::load()['tables'] as $table) {
            if ($table['source_table'] === $sourceTable) {
                return $table;
            }
        }

        throw new RuntimeException('Source table has no approved shared operational payload.');
    }

    public static function quote(string $identifier): string
    {
        return PreservationManifest::quote($identifier);
    }

    /** Globally unique deterministic constraint/index names, all below 64 bytes. */
    public static function objectName(string $kind, string $table, int $ordinal): string
    {
        if (! in_array($kind, ['ix', 'fk', 'ck', 'alter'], true) || $ordinal < 0) {
            throw new RuntimeException('Invalid operational schema object kind.');
        }

        return 'op6_'.$kind.'_'.substr(hash('sha256', $table.':'.$ordinal), 0, 24);
    }

    public static function columnList(array $columns): string
    {
        return implode(', ', array_map(self::quote(...), $columns));
    }

    /** Create columns, indexes and CHECKs first; attach FKs after every table exists. */
    public static function createTableSql(array $table): string
    {
        $parts = [];
        foreach ($table['columns'] as $column) {
            $parts[] = self::quote($column['name']).' '.$column['definition'];
        }
        foreach ($table['indexes'] as $ordinal => $index) {
            $parts[] = self::indexSql($table['name'], $ordinal, $index);
        }
        foreach ($table['checks'] as $ordinal => $check) {
            $parts[] = 'CONSTRAINT '.self::quote(self::objectName('ck', $table['name'], $ordinal)).' CHECK ('.$check.')';
        }

        return 'CREATE TABLE '.self::quote($table['name']).' ('.implode(",\n", $parts).') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin';
    }

    public static function indexSql(string $table, int $ordinal, array $index, string $kind = 'ix'): string
    {
        $columns = '('.self::columnList($index['columns']).')';
        if ($index['kind'] === 'PRIMARY') {
            return 'PRIMARY KEY '.$columns;
        }
        if (! in_array($index['kind'], ['UNIQUE', 'INDEX'], true)) {
            throw new RuntimeException('Unsupported approved index type.');
        }

        return ($index['kind'] === 'UNIQUE' ? 'UNIQUE KEY ' : 'KEY ').self::quote(self::objectName($kind, $table, $ordinal)).' '.$columns;
    }

    public static function foreignKeySql(array $table, int $ordinal, array $foreign): string
    {
        if ($foreign['actions'] !== 'ON DELETE RESTRICT ON UPDATE RESTRICT') {
            throw new RuntimeException('Operational relationships must retain RESTRICT actions.');
        }

        return 'ALTER TABLE '.self::quote($table['name']).' ADD CONSTRAINT '
            .self::quote(self::objectName('fk', $table['name'], $ordinal))
            .' FOREIGN KEY ('.self::columnList($foreign['columns']).') REFERENCES '
            .self::quote($foreign['parent_table']).' ('.self::columnList($foreign['parent_columns']).') '
            .$foreign['actions'];
    }
}
