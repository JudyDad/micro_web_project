<?php

namespace Tests\Unit;

use Database\Schema\PreservationManifest;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../database/schema/PreservationManifest.php';

class PreservationManifestTest extends TestCase
{
    public function test_all_approved_tables_can_compile_including_arabic_column_names(): void
    {
        $manifest = PreservationManifest::load();
        $ids = PreservationManifest::tableIds($manifest);
        self::assertCount(616, $manifest['tables']);
        self::assertCount(127, $ids);
        $columnCount = 0;
        foreach ($manifest['tables'] as $table) {
            $sql = PreservationManifest::createTableSql($table, PreservationManifest::sourceId($table['city']), $ids[$table['source_table']]);
            self::assertStringNotContainsString('AUTO_INCREMENT', $sql);
            self::assertStringContainsString('REFERENCES `source_records`', $sql);
            foreach ($table['columns'] as $column) {
                self::assertStringContainsString(PreservationManifest::quote($column['target_column']).' '.$column['target_definition'], $sql);
                $columnCount++;
            }
        }
        self::assertSame(3947, $columnCount);
    }
}
