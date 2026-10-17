<?php

use Database\Schema\PreservationRollbackGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../schema/PreservationRollbackGuard.php';

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();

        // Explicit SQL preserves the approved binary lengths and composite keys.
        // Status vocabularies, run/snapshot consistency, digest collision checks,
        // and relationship snapshot consistency remain importer responsibilities.
        $definitions = [
            'cities' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(16) NOT NULL,
                name_en VARCHAR(100) NOT NULL,
                name_ar VARCHAR(100) NOT NULL,
                UNIQUE KEY cities_code_unique (code)
                SQL,
            'sources' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                city_id BIGINT UNSIGNED NOT NULL,
                code VARCHAR(64) NOT NULL,
                UNIQUE KEY sources_code_unique (code),
                UNIQUE KEY sources_id_city_unique (id, city_id),
                KEY sources_city_index (city_id),
                CONSTRAINT sources_city_fk FOREIGN KEY (city_id)
                    REFERENCES cities (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'source_tables' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARBINARY(64) NOT NULL,
                UNIQUE KEY source_tables_name_unique (name)
                SQL,
            'snapshots' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                archive_sha256 BINARY(32) NOT NULL,
                sql_sha256 BINARY(32) NOT NULL,
                manifest_locator VARBINARY(1024) NOT NULL,
                captured_at DATETIME(6) NULL,
                UNIQUE KEY snapshots_source_sql_unique (source_id, sql_sha256),
                UNIQUE KEY snapshots_source_id_unique (source_id, id),
                CONSTRAINT snapshots_source_fk FOREIGN KEY (source_id)
                    REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'migration_runs' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                snapshot_id BIGINT UNSIGNED NOT NULL,
                tool_version VARCHAR(100) NOT NULL,
                started_at DATETIME(6) NOT NULL,
                completed_at DATETIME(6) NULL,
                status VARCHAR(32) NOT NULL,
                evidence_locator VARBINARY(1024) NULL,
                KEY migration_runs_snapshot_status_index (snapshot_id, status),
                CONSTRAINT migration_runs_snapshot_fk FOREIGN KEY (snapshot_id)
                    REFERENCES snapshots (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'source_records' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                table_id BIGINT UNSIGNED NOT NULL,
                snapshot_id BIGINT UNSIGNED NOT NULL,
                source_key LONGBLOB NOT NULL,
                key_sha256 BINARY(32) NOT NULL,
                key_kind VARCHAR(16) NOT NULL,
                import_run_id BIGINT UNSIGNED NOT NULL,
                resolution_status VARCHAR(32) NOT NULL,
                UNIQUE KEY source_records_occurrence_unique (source_id, table_id, snapshot_id, key_sha256),
                UNIQUE KEY source_records_source_table_id_unique (source_id, table_id, id),
                KEY source_records_source_snapshot_index (source_id, snapshot_id),
                KEY source_records_table_index (table_id),
                KEY source_records_import_run_index (import_run_id),
                CONSTRAINT source_records_snapshot_fk FOREIGN KEY (source_id, snapshot_id)
                    REFERENCES snapshots (source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT source_records_table_fk FOREIGN KEY (table_id)
                    REFERENCES source_tables (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT source_records_import_run_fk FOREIGN KEY (import_run_id)
                    REFERENCES migration_runs (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'relationship_contracts' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                child_table_id BIGINT UNSIGNED NOT NULL,
                parent_table_id BIGINT UNSIGNED NOT NULL,
                name VARBINARY(128) NOT NULL,
                child_columns_json JSON NOT NULL,
                parent_columns_json JSON NOT NULL,
                evidence_locator VARBINARY(1024) NOT NULL,
                contract_kind VARCHAR(32) NOT NULL,
                UNIQUE KEY relationship_contracts_name_unique (source_id, child_table_id, name),
                UNIQUE KEY relationship_contracts_tuple_unique (id, source_id, child_table_id, parent_table_id),
                KEY relationship_contracts_child_table_index (child_table_id),
                KEY relationship_contracts_parent_table_index (parent_table_id),
                CONSTRAINT relationship_contracts_source_fk FOREIGN KEY (source_id)
                    REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT relationship_contracts_child_table_fk FOREIGN KEY (child_table_id)
                    REFERENCES source_tables (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT relationship_contracts_parent_table_fk FOREIGN KEY (parent_table_id)
                    REFERENCES source_tables (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'record_relationships' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                contract_id BIGINT UNSIGNED NOT NULL,
                source_id BIGINT UNSIGNED NOT NULL,
                child_table_id BIGINT UNSIGNED NOT NULL,
                child_record_id BIGINT UNSIGNED NOT NULL,
                parent_table_id BIGINT UNSIGNED NOT NULL,
                parent_record_id BIGINT UNSIGNED NULL,
                status VARCHAR(32) NOT NULL,
                evidence_locator VARBINARY(1024) NULL,
                UNIQUE KEY record_relationships_contract_child_unique (contract_id, child_record_id),
                KEY record_relationships_contract_index (contract_id, source_id, child_table_id, parent_table_id),
                KEY record_relationships_child_index (source_id, child_table_id, child_record_id),
                KEY record_relationships_parent_index (source_id, parent_table_id, parent_record_id),
                CONSTRAINT record_relationships_contract_fk FOREIGN KEY (contract_id, source_id, child_table_id, parent_table_id)
                    REFERENCES relationship_contracts (id, source_id, child_table_id, parent_table_id)
                    ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT record_relationships_child_fk FOREIGN KEY (source_id, child_table_id, child_record_id)
                    REFERENCES source_records (source_id, table_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT record_relationships_parent_fk FOREIGN KEY (source_id, parent_table_id, parent_record_id)
                    REFERENCES source_records (source_id, table_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT record_relationships_resolution_check CHECK (
                    (status = 'resolved' AND parent_record_id IS NOT NULL)
                    OR (status <> 'resolved' AND parent_record_id IS NULL)
                )
                SQL,
            'migration_exceptions' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                run_id BIGINT UNSIGNED NOT NULL,
                record_id BIGINT UNSIGNED NULL,
                relationship_id BIGINT UNSIGNED NULL,
                category VARCHAR(64) NOT NULL,
                state VARCHAR(32) NOT NULL,
                evidence_locator VARBINARY(1024) NOT NULL,
                decision_text TEXT NULL,
                reviewed_by VARCHAR(128) NULL,
                reviewed_at DATETIME(6) NULL,
                KEY migration_exceptions_run_state_category_index (run_id, state, category),
                KEY migration_exceptions_record_index (record_id),
                KEY migration_exceptions_relationship_index (relationship_id),
                CONSTRAINT migration_exceptions_run_fk FOREIGN KEY (run_id)
                    REFERENCES migration_runs (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT migration_exceptions_record_fk FOREIGN KEY (record_id)
                    REFERENCES source_records (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT migration_exceptions_relationship_fk FOREIGN KEY (relationship_id)
                    REFERENCES record_relationships (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'project_number_sequences' => <<<'SQL'
                source_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                next_number BIGINT UNSIGNED NOT NULL,
                CONSTRAINT project_number_sequences_source_fk FOREIGN KEY (source_id)
                    REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
        ];

        $created = [];

        try {
            foreach ($definitions as $table => $definition) {
                DB::statement("CREATE TABLE `{$table}` ({$definition}) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
                $created[] = $table;
            }
        } catch (Throwable $exception) {
            // MySQL DDL commits implicitly. Only remove tables this invocation
            // actually created; a conflicting pre-existing table stays intact.
            foreach (array_reverse($created) as $table) {
                DB::statement("DROP TABLE `{$table}`");
            }

            throw $exception;
        }
    }

    public function down(): void
    {
        $this->requireMysql();
        PreservationRollbackGuard::assertEmptyEvidence();

        foreach ([
            'project_number_sequences', 'migration_exceptions', 'record_relationships',
            'relationship_contracts', 'source_records', 'migration_runs',
            'snapshots', 'source_tables', 'sources', 'cities',
        ] as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('The preservation schema requires MySQL.');
        }
    }
};
