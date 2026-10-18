<?php

use Database\Schema\OperationalManifest;
use Database\Schema\OperationalRollbackGuard;
use Database\Schema\PreservationManifest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../schema/OperationalManifest.php';
require_once __DIR__.'/../schema/OperationalRollbackGuard.php';

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();
        $manifest = OperationalManifest::load();
        $this->assertCatalog();
        $tables = array_merge($manifest['tables'], $manifest['support_tables']);
        $created = $altered = $attached = [];
        try {
            foreach ($manifest['existing_table_alterations'] as $alteration) {
                foreach ($alteration['add_indexes'] as $ordinal => $index) {
                    DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' ADD '
                        .OperationalManifest::indexSql($alteration['table'], $ordinal, $index, 'alter'));
                    $altered[] = [$alteration['table'], OperationalManifest::objectName('alter', $alteration['table'], $ordinal)];
                }
            }
            foreach ($tables as $table) {
                DB::statement(OperationalManifest::createTableSql($table));
                $created[] = $table['name'];
            }
            foreach ($tables as $table) {
                foreach ($table['foreign_keys'] as $ordinal => $foreign) {
                    DB::statement(OperationalManifest::foreignKeySql($table, $ordinal, $foreign));
                    $attached[] = [$table['name'], OperationalManifest::objectName('fk', $table['name'], $ordinal)];
                }
            }
        } catch (Throwable $exception) {
            // MySQL DDL commits implicitly. Only remove artifacts created by this
            // invocation, and refuse cleanup if any of those tables acquired rows.
            OperationalRollbackGuard::assertEmptyTables($created);
            foreach (array_reverse($attached) as [$table, $constraint]) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($table).' DROP FOREIGN KEY '.OperationalManifest::quote($constraint));
            }
            foreach (array_reverse($created) as $table) {
                DB::statement('DROP TABLE '.OperationalManifest::quote($table));
            }
            foreach (array_reverse($altered) as [$table, $index]) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($table).' DROP INDEX '.OperationalManifest::quote($index));
            }
            throw $exception;
        }
    }

    public function down(): void
    {
        $this->requireMysql();
        $manifest = OperationalManifest::load();
        $tables = array_merge($manifest['tables'], $manifest['support_tables']);
        OperationalRollbackGuard::assertEmptyEvidence($tables);
        foreach (array_reverse($tables) as $table) {
            foreach (array_reverse(array_keys($table['foreign_keys'])) as $ordinal) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($table['name']).' DROP FOREIGN KEY '
                    .OperationalManifest::quote(OperationalManifest::objectName('fk', $table['name'], $ordinal)));
            }
        }
        foreach (array_reverse($tables) as $table) {
            DB::statement('DROP TABLE '.OperationalManifest::quote($table['name']));
        }
        foreach (array_reverse($manifest['existing_table_alterations']) as $alteration) {
            foreach (array_reverse(array_keys($alteration['add_indexes'])) as $ordinal) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' DROP INDEX '
                    .OperationalManifest::quote(OperationalManifest::objectName('alter', $alteration['table'], $ordinal)));
            }
        }
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Shared operational schema requires MySQL.');
        }
        $version = DB::selectOne('SELECT VERSION() AS version')->version;
        if (str_contains($version, 'MariaDB') || version_compare($version, '8.0.16', '<')) {
            throw new RuntimeException('Shared operational schema requires MySQL with enforced CHECK constraints.');
        }
    }

    private function assertCatalog(): void
    {
        $tables = DB::table('source_tables')->pluck('name', 'id')->all();
        foreach (PreservationManifest::tableIds(PreservationManifest::load()) as $name => $id) {
            if (($tables[$id] ?? null) !== $name) {
                throw new RuntimeException('Operational source-table catalog identity mismatch.');
            }
        }
        $sources = DB::table('sources')->get()->keyBy('id');
        $cities = DB::table('cities')->pluck('code', 'id')->all();
        foreach (PreservationManifest::CITIES as $id => $city) {
            if (! isset($sources[$id]) || $sources[$id]->code !== $city['code']
                || (int) $sources[$id]->city_id !== $id || ($cities[$id] ?? null) !== $city['code']) {
                throw new RuntimeException('Operational source/city catalog identity mismatch.');
            }
        }
    }
};
