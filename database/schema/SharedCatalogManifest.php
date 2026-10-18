<?php

declare(strict_types=1);

namespace Database\Schema;

use RuntimeException;

require_once __DIR__.'/OperationalManifest.php';

final class SharedCatalogManifest
{
    public const SHA256 = '6a7222a837f9a5fb882155227e8e30c0fa660a77442c0786e05ba2ed16b5bc51';

    private static ?array $manifest = null;

    private const SOURCE_BASELINES = [
        'Aleppo' => ['archive' => 'hcsyria_micro202610161513.zip', 'archive_sha256' => 'e18c71e5d7f96bd0cab234ed8e7efbfcbdb1e7ac5e3d055f49d7624e27c45d0c', 'sql_sha256' => '93df400f05395078b4942e34caf87fa1456b7852bd440caff02b61e149d71b7d'],
        'Damascus' => ['archive' => 'hcsyria_micro_d202610161515.zip', 'archive_sha256' => 'dd69edef26e806db204d002bbeab36708a7f118e0ca0df6c117f3715dc49c2ee', 'sql_sha256' => '03c000802f29047872a3c0f597137929c5778eb9e4c52aaa9aed4fce06d42af0'],
        'Homs' => ['archive' => 'hcsyria_micro_h202610161517.zip', 'archive_sha256' => '4fb0edbc864ff02f679bc314665c16a4a3c663eb988d2dfd343225e8f23267f5', 'sql_sha256' => '557d0f5b3169cdaab53fb1cd4258436d09422fd9eabf32807c4ec65980d1d6f1'],
        'Latakia' => ['archive' => 'hcsyria_micro_l202610161520.zip', 'archive_sha256' => '36b35963af03859ea8b6e86c0360ac70d4094d1d1a1ecd9d1befd474f41d4fcd', 'sql_sha256' => '0e963b486eac1d658b51b940e0138bc342971b7bbcf37f1f9fc9a889f391c9ff'],
        'AlWadi' => ['archive' => 'hcsyria_micro_w202610161522.zip', 'archive_sha256' => '3d16350305423d76333b1da0abb3b34624732445f9994559d9d5292ecb1b22da', 'sql_sha256' => '16e21462e95e24bc047723b4c0ad358893391572c96ecce1c6047e95deb08648'],
    ];

    public static function load(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }
        $path = __DIR__.'/shared-catalog-v1.json';
        if (! is_file($path) || hash_file('sha256', $path) !== self::SHA256) {
            throw new RuntimeException('Approved shared catalog manifest changed; use a new reviewed migration.');
        }
        OperationalManifest::load();
        $manifest = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if ($manifest['base_schema_sha256'] !== OperationalManifest::SHA256
            || count($manifest['canonical_tables']) !== 55 || count($manifest['support_tables']) !== 2
            || count($manifest['operational_table_alterations']) !== 55 || count($manifest['candidate_groups']) !== 58) {
            throw new RuntimeException('Shared catalog base schema contract mismatch.');
        }

        return self::$manifest = $manifest;
    }

    public static function tables(): array
    {
        $manifest = self::load();

        return array_merge($manifest['support_tables'], $manifest['canonical_tables']);
    }

    public static function table(string $sourceTable): array
    {
        foreach (self::load()['canonical_tables'] as $table) {
            if ($table['name'] === 'shared_'.$sourceTable) {
                return $table;
            }
        }

        throw new RuntimeException('Source table is outside approved shared catalog scope.');
    }

    public static function groups(?string $sourceTable = null): array
    {
        return array_values(array_filter(self::load()['candidate_groups'], fn (array $group) => $sourceTable === null || $group['table'] === $sourceTable));
    }

    public static function alterations(): array
    {
        return self::load()['operational_table_alterations'];
    }

    public static function sourceBaseline(string $city): array
    {
        if (! isset(self::SOURCE_BASELINES[$city])) {
            throw new RuntimeException('Unknown approved sharing source city.');
        }

        return self::SOURCE_BASELINES[$city];
    }

    public static function sourceBaselines(): array
    {
        return self::SOURCE_BASELINES;
    }

    public static function approvedGroup(string $sourceTable, array $cities): array
    {
        sort($cities, SORT_STRING);
        foreach (self::groups($sourceTable) as $group) {
            $members = $group['cities'];
            sort($members, SORT_STRING);
            if ($cities === $members) {
                return $group;
            }
        }

        throw new RuntimeException('No approved whole-table sharing group matches these cities.');
    }

    public static function sourceTable(string $city, string $sourceTable): array
    {
        foreach (PreservationManifest::load()['tables'] as $table) {
            if ($table['city'] === $city && $table['source_table'] === $sourceTable) {
                return $table;
            }
        }

        throw new RuntimeException('Unknown preserved source table.');
    }

    public static function objectName(string $kind, string $table, int $ordinal): string
    {
        if (! in_array($kind, ['ix', 'fk', 'ck', 'alter'], true) || $ordinal < 0) {
            throw new RuntimeException('Invalid shared schema object kind.');
        }

        return 'sc7_'.$kind.'_'.substr(hash('sha256', $table.':'.$ordinal), 0, 24);
    }

    public static function createTableSql(array $table): string
    {
        $parts = [];
        foreach ($table['columns'] as $column) {
            $parts[] = OperationalManifest::quote($column['name']).' '.$column['definition'];
        }
        foreach ($table['indexes'] as $ordinal => $index) {
            $parts[] = self::indexSql($table['name'], $ordinal, $index);
        }
        foreach ($table['checks'] as $ordinal => $check) {
            $parts[] = 'CONSTRAINT '.OperationalManifest::quote(self::objectName('ck', $table['name'], $ordinal)).' CHECK ('.$check.')';
        }

        return 'CREATE TABLE '.OperationalManifest::quote($table['name']).' ('.implode(",\n", $parts).') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin';
    }

    public static function indexSql(string $table, int $ordinal, array $index, string $kind = 'ix'): string
    {
        $columns = '('.OperationalManifest::columnList($index['columns']).')';
        if ($index['kind'] === 'PRIMARY') {
            return 'PRIMARY KEY '.$columns;
        }
        if (! in_array($index['kind'], ['UNIQUE', 'INDEX'], true)) {
            throw new RuntimeException('Unsupported shared index type.');
        }

        return ($index['kind'] === 'UNIQUE' ? 'UNIQUE KEY ' : 'KEY ').OperationalManifest::quote(self::objectName($kind, $table, $ordinal)).' '.$columns;
    }

    public static function foreignKeySql(string $table, int $ordinal, array $foreign): string
    {
        if ($foreign['actions'] !== 'ON DELETE RESTRICT ON UPDATE RESTRICT') {
            throw new RuntimeException('Shared relationships must retain RESTRICT actions.');
        }

        return 'ALTER TABLE '.OperationalManifest::quote($table).' ADD CONSTRAINT '
            .OperationalManifest::quote(self::objectName('fk', $table, $ordinal))
            .' FOREIGN KEY ('.OperationalManifest::columnList($foreign['columns']).') REFERENCES '
            .OperationalManifest::quote($foreign['parent_table']).' ('.OperationalManifest::columnList($foreign['parent_columns']).') '.$foreign['actions'];
    }
}
