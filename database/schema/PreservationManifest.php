<?php

namespace Database\Schema;

use RuntimeException;

final class PreservationManifest
{
    public const SHA256 = '5e46874d8ee93e72d36d05986fe95154e2c6d6650e908ba1dccbb0cbb2ca8e5d';

    public const CITIES = [
        1 => ['code' => 'aleppo', 'name_en' => 'Aleppo', 'name_ar' => 'حلب'],
        2 => ['code' => 'damascus', 'name_en' => 'Damascus', 'name_ar' => 'دمشق'],
        3 => ['code' => 'homs', 'name_en' => 'Homs', 'name_ar' => 'حمص'],
        4 => ['code' => 'latakia', 'name_en' => 'Latakia', 'name_ar' => 'اللاذقية'],
        5 => ['code' => 'alwadi', 'name_en' => 'AlWadi', 'name_ar' => 'الوادي'],
    ];

    public static function load(): array
    {
        $path = __DIR__.'/preservation-v1.json';
        if (hash_file('sha256', $path) !== self::SHA256) {
            throw new RuntimeException('Approved preservation manifest changed; use a new reviewed migration.');
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function tableIds(array $manifest): array
    {
        $names = array_unique(array_column($manifest['tables'], 'source_table'));
        sort($names, SORT_STRING);

        return array_combine($names, range(1, count($names)));
    }

    public static function sourceId(string $city): int
    {
        foreach (self::CITIES as $id => $definition) {
            if ($definition['name_en'] === $city) {
                return $id;
            }
        }

        throw new RuntimeException('Unknown source city in approved manifest.');
    }

    public static function quote(string $identifier): string
    {
        if ($identifier === '' || preg_match('//u', $identifier) !== 1 || str_contains($identifier, "\0") || mb_strlen($identifier, 'UTF-8') > 64) {
            throw new RuntimeException('Invalid schema identifier.');
        }

        return '`'.str_replace('`', '``', $identifier).'`';
    }

    public static function createTableSql(array $table, int $sourceId, int $tableId): string
    {
        $columns = [
            '`record_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY',
            '`origin_source_id` BIGINT UNSIGNED NOT NULL',
            '`origin_table_id` BIGINT UNSIGNED NOT NULL',
        ];
        foreach ($table['columns'] as $column) {
            $columns[] = self::quote($column['target_column']).' '.$column['target_definition'];
        }
        $columns = array_merge($columns, $table['target_indexes']);
        $columns[] = 'KEY `registry_identity` (`origin_source_id`, `origin_table_id`, `record_id`)';
        $constraint = 'preservation_'.$sourceId.'_'.$tableId;
        $columns[] = 'CONSTRAINT '.self::quote($constraint.'_source').' CHECK (`origin_source_id` = '.$sourceId.')';
        $columns[] = 'CONSTRAINT '.self::quote($constraint.'_table').' CHECK (`origin_table_id` = '.$tableId.')';
        $columns[] = 'CONSTRAINT '.self::quote($constraint.'_registry').' FOREIGN KEY (`origin_source_id`, `origin_table_id`, `record_id`) REFERENCES `source_records` (`source_id`, `table_id`, `id`) ON DELETE RESTRICT ON UPDATE RESTRICT';

        if (! preg_match('/DEFAULT CHARSET=(utf8mb[34])(?: COLLATE=([a-z0-9_]+))?/', $table['original_ddl'], $options)) {
            throw new RuntimeException('Missing source charset.');
        }
        $charset = $options[1];
        $collation = $options[2] ?? 'utf8mb3_general_ci';

        return 'CREATE TABLE '.self::quote($table['target_table']).' ('.implode(",\n", $columns).') ENGINE=InnoDB DEFAULT CHARSET='.$charset.' COLLATE='.$collation;
    }
}
