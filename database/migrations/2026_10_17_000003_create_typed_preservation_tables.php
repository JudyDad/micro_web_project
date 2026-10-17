<?php

use Database\Schema\PreservationManifest;
use Database\Schema\PreservationRollbackGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../schema/PreservationManifest.php';
require_once __DIR__.'/../schema/PreservationRollbackGuard.php';

return new class extends Migration
{
    public function up(): void
    {
        $manifest = PreservationManifest::load();
        $tableIds = PreservationManifest::tableIds($manifest);
        $created = [];
        try {
            foreach ($manifest['tables'] as $table) {
                $sourceId = PreservationManifest::sourceId($table['city']);
                // Catalog ids are part of the approved source/table CHECK contract.
                if (DB::table('sources')->where('id', $sourceId)->value('code') !== PreservationManifest::CITIES[$sourceId]['code']
                    || DB::table('source_tables')->where('id', $tableIds[$table['source_table']])->value('name') !== $table['source_table']) {
                    throw new RuntimeException('Preservation catalog identity mismatch.');
                }
                DB::statement(PreservationManifest::createTableSql($table, $sourceId, $tableIds[$table['source_table']]));
                $created[] = $table['target_table'];
            }
        } catch (Throwable $exception) {
            // MySQL DDL is not transactional. Clean up only tables created here.
            foreach (array_reverse($created) as $table) {
                DB::statement('DROP TABLE '.PreservationManifest::quote($table));
            }
            throw $exception;
        }
    }

    public function down(): void
    {
        PreservationRollbackGuard::assertEmptyEvidence();
        $tables = PreservationManifest::load()['tables'];
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'), 'name');
        // Check all evidence before the first non-transactional DROP, including
        // typed rows whose registry link could have been corrupted outside the app.
        if (in_array('source_records', $existing, true) && DB::table('source_records')->exists()) {
            throw new RuntimeException('Preservation rollback requires an empty registry; imported evidence must be retained.');
        }
        foreach ($tables as $table) {
            if (in_array($table['target_table'], $existing, true) && DB::table($table['target_table'])->exists()) {
                throw new RuntimeException('Preservation rollback refuses to drop populated typed tables.');
            }
        }
        foreach (array_reverse($tables) as $table) {
            DB::statement('DROP TABLE IF EXISTS '.PreservationManifest::quote($table['target_table']));
        }
    }
};
