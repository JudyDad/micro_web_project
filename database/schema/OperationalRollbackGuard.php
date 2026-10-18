<?php

declare(strict_types=1);

namespace Database\Schema;

use Illuminate\Support\Facades\DB;
use RuntimeException;

require_once __DIR__.'/PreservationRollbackGuard.php';

final class OperationalRollbackGuard
{
    public static function assertEmptyTables(array $tables): void
    {
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'), 'name');
        foreach ($tables as $table) {
            $name = is_array($table) ? $table['name'] : $table;
            if (in_array($name, $existing, true) && DB::table($name)->exists()) {
                throw new RuntimeException('Operational rollback refuses retained rows in '.$name.'.');
            }
        }
    }

    public static function assertEmptyEvidence(array $tables): void
    {
        // Complete every read-only preflight before the first nontransactional DDL.
        PreservationRollbackGuard::assertEmptyEvidence();
        self::assertEmptyTables($tables);
    }
}
