<?php

namespace Database\Schema;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PreservationRollbackGuard
{
    public static function assertEmptyEvidence(): void
    {
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'), 'name');
        foreach (['snapshots', 'migration_runs', 'source_records', 'relationship_contracts', 'record_relationships', 'migration_exceptions', 'project_number_sequences'] as $table) {
            if (in_array($table, $existing, true) && DB::table($table)->exists()) {
                throw new RuntimeException('Preservation rollback refuses retained evidence in '.$table.'.');
            }
        }
    }
}
