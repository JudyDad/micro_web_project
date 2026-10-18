<?php

declare(strict_types=1);

namespace Database\Schema;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class SharedCatalogRollbackGuard
{
    /** All checks finish before any nontransactional constraint/index/table DROP. */
    public static function assertEmptyEvidence(array $tables, array $alterations): void
    {
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'), 'name');
        foreach ($tables as $table) {
            $name = is_array($table) ? $table['name'] : $table;
            if (in_array($name, $existing, true) && DB::table($name)->exists()) {
                throw new RuntimeException('Shared catalog rollback refuses retained rows in '.$name.'.');
            }
        }
        foreach ($alterations as $alteration) {
            $name = $alteration['table'];
            if (! in_array($name, $existing, true)) {
                continue;
            }
            if (DB::table($name)->whereNotNull('shared_group_id')->orWhereNotNull('shared_row_id')->orWhereNotNull('shared_snapshot_id')->exists()) {
                throw new RuntimeException('Shared catalog rollback refuses retained aliases in '.$name.'.');
            }
        }
    }
}
