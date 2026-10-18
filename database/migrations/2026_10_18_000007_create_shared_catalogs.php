<?php

use Database\Schema\OperationalManifest;
use Database\Schema\SharedCatalogManifest;
use Database\Schema\SharedCatalogRollbackGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../schema/SharedCatalogManifest.php';
require_once __DIR__.'/../schema/SharedCatalogRollbackGuard.php';

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();
        $manifest = SharedCatalogManifest::load();
        $created = $altered = $indices = $attached = [];
        try {
            foreach ($manifest['existing_table_alterations'] as $alteration) {
                foreach ($alteration['add_indexes'] as $ordinal => $index) {
                    DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' ADD '.SharedCatalogManifest::indexSql($alteration['table'], $ordinal, $index, 'alter'));
                    $indices[] = [$alteration['table'], SharedCatalogManifest::objectName('alter', $alteration['table'], $ordinal)];
                }
            }
            foreach (SharedCatalogManifest::tables() as $table) {
                DB::statement(SharedCatalogManifest::createTableSql($table));
                $created[] = $table['name'];
            }
            foreach ($manifest['operational_table_alterations'] as $alteration) {
                $clauses = [];
                foreach ($alteration['add_columns'] as $column) {
                    $clauses[] = 'ADD COLUMN '.OperationalManifest::quote($column['name']).' '.$column['definition'];
                }
                foreach ($alteration['add_indexes'] as $ordinal => $index) {
                    $clauses[] = 'ADD '.SharedCatalogManifest::indexSql($alteration['table'], $ordinal, $index);
                }
                foreach ($alteration['add_checks'] as $ordinal => $check) {
                    $clauses[] = 'ADD CONSTRAINT '.OperationalManifest::quote(SharedCatalogManifest::objectName('ck', $alteration['table'], $ordinal)).' CHECK ('.$check.')';
                }
                // One atomic ALTER per table; cleanup only records completed DDL.
                DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' '.implode(', ', $clauses));
                $altered[] = $alteration;
            }
            foreach (SharedCatalogManifest::tables() as $table) {
                foreach ($table['foreign_keys'] as $ordinal => $foreign) {
                    DB::statement(SharedCatalogManifest::foreignKeySql($table['name'], $ordinal, $foreign));
                    $attached[] = [$table['name'], SharedCatalogManifest::objectName('fk', $table['name'], $ordinal)];
                }
            }
            foreach ($altered as $alteration) {
                foreach ($alteration['add_foreign_keys'] as $ordinal => $foreign) {
                    DB::statement(SharedCatalogManifest::foreignKeySql($alteration['table'], $ordinal, $foreign));
                    $attached[] = [$alteration['table'], SharedCatalogManifest::objectName('fk', $alteration['table'], $ordinal)];
                }
            }
        } catch (Throwable $exception) {
            SharedCatalogRollbackGuard::assertEmptyEvidence($created, $altered);
            $this->dropForeignKeys(array_reverse($attached));
            foreach (array_reverse($altered) as $alteration) {
                $this->removeAlteration($alteration);
            }
            foreach (array_reverse($created) as $table) {
                DB::statement('DROP TABLE '.OperationalManifest::quote($table));
            }
            foreach (array_reverse($indices) as [$table, $index]) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($table).' DROP INDEX '.OperationalManifest::quote($index));
            }
            throw $exception;
        }
    }

    public function down(): void
    {
        $this->requireMysql();
        $manifest = SharedCatalogManifest::load();
        $tables = SharedCatalogManifest::tables();
        $alterations = SharedCatalogManifest::alterations();
        SharedCatalogRollbackGuard::assertEmptyEvidence($tables, $alterations);
        $foreignKeys = [];
        foreach ($alterations as $alteration) {
            foreach ($alteration['add_foreign_keys'] as $ordinal => $foreign) {
                $foreignKeys[] = [$alteration['table'], SharedCatalogManifest::objectName('fk', $alteration['table'], $ordinal)];
            }
        }
        foreach ($tables as $table) {
            foreach ($table['foreign_keys'] as $ordinal => $foreign) {
                $foreignKeys[] = [$table['name'], SharedCatalogManifest::objectName('fk', $table['name'], $ordinal)];
            }
        }
        $this->dropForeignKeys(array_reverse($foreignKeys));
        foreach (array_reverse($alterations) as $alteration) {
            $this->removeAlteration($alteration);
        }
        foreach (array_reverse($tables) as $table) {
            DB::statement('DROP TABLE '.OperationalManifest::quote($table['name']));
        }
        foreach (array_reverse($manifest['existing_table_alterations']) as $alteration) {
            foreach (array_reverse(array_keys($alteration['add_indexes'])) as $ordinal) {
                DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' DROP INDEX '.OperationalManifest::quote(SharedCatalogManifest::objectName('alter', $alteration['table'], $ordinal)));
            }
        }
    }

    private function dropForeignKeys(array $foreignKeys): void
    {
        foreach ($foreignKeys as [$table, $constraint]) {
            DB::statement('ALTER TABLE '.OperationalManifest::quote($table).' DROP FOREIGN KEY '.OperationalManifest::quote($constraint));
        }
    }

    private function removeAlteration(array $alteration): void
    {
        $clauses = [];
        foreach (array_keys($alteration['add_checks']) as $ordinal) {
            $clauses[] = 'DROP CHECK '.OperationalManifest::quote(SharedCatalogManifest::objectName('ck', $alteration['table'], $ordinal));
        }
        foreach (array_keys($alteration['add_indexes']) as $ordinal) {
            $clauses[] = 'DROP INDEX '.OperationalManifest::quote(SharedCatalogManifest::objectName('ix', $alteration['table'], $ordinal));
        }
        foreach ($alteration['add_columns'] as $column) {
            $clauses[] = 'DROP COLUMN '.OperationalManifest::quote($column['name']);
        }
        DB::statement('ALTER TABLE '.OperationalManifest::quote($alteration['table']).' '.implode(', ', $clauses));
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Shared catalogs require MySQL.');
        }
        $version = DB::selectOne('SELECT VERSION() AS version')->version;
        if (str_contains($version, 'MariaDB') || version_compare($version, '8.0.16', '<')) {
            throw new RuntimeException('Shared catalogs require MySQL with enforced CHECK constraints.');
        }
    }
};
