<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Approved by the owner on 22 October 2026 (docs/planning/phase-5/imported-record-deletion-proposal.md): imported records are never
 * physically deleted. A row here hides one operational entity from the web; the preserved source row, the entity and its origins stay
 * untouched, so the record can be restored by removing the row.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();
        DB::statement(<<<'SQL'
            CREATE TABLE `operational_deletions` (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                city_id BIGINT UNSIGNED NOT NULL,
                source_id BIGINT UNSIGNED NOT NULL,
                source_table_id BIGINT UNSIGNED NOT NULL,
                entity_id BIGINT UNSIGNED NOT NULL,
                deleted_by_account_id BIGINT UNSIGNED NOT NULL,
                deleted_at DATETIME(6) NOT NULL,
                reason TEXT NULL DEFAULT NULL,
                UNIQUE KEY od_entity_unique (source_table_id, entity_id),
                KEY od_city_index (city_id, source_id, source_table_id),
                KEY od_account_index (deleted_by_account_id),
                CONSTRAINT od_entity_fk FOREIGN KEY (city_id, source_id, source_table_id, entity_id) REFERENCES operational_entities (city_id, source_id, source_table_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT od_account_fk FOREIGN KEY (deleted_by_account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
            SQL);
    }

    public function down(): void
    {
        $this->requireMysql();
        if (DB::table('operational_deletions')->exists()) {
            throw new RuntimeException('Deletion records are retained evidence; restore or preserve them before rollback.');
        }
        DB::statement('DROP TABLE `operational_deletions`');
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Operational deletions require MySQL.');
        }
    }
};
