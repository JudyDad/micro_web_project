<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();
        if ((int) DB::table('source_tables')->where('name', 'user')->value('id') !== 117) {
            throw new RuntimeException('Approved source user catalog identity mismatch.');
        }
        $definitions = [
            'web_principals' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                source_table_id BIGINT UNSIGNED NOT NULL,
                source_record_id BIGINT UNSIGNED NOT NULL,
                password_hash VARCHAR(255) NULL DEFAULT NULL,
                disabled_at DATETIME(6) NULL DEFAULT NULL,
                created_at DATETIME(6) NOT NULL,
                updated_at DATETIME(6) NOT NULL,
                UNIQUE KEY wp_source_record_unique (source_id, source_table_id, source_record_id),
                UNIQUE KEY wp_source_id_unique (source_id, id),
                CONSTRAINT wp_user_table_check CHECK (source_table_id = 117),
                CONSTRAINT wp_source_fk FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wp_record_fk FOREIGN KEY (source_id, source_table_id, source_record_id)
                    REFERENCES source_records (source_id, table_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT
                SQL,
            'principal_capabilities' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                principal_id BIGINT UNSIGNED NOT NULL,
                capability VARCHAR(64) NOT NULL,
                decision_locator VARBINARY(1024) NOT NULL,
                granted_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY pc_source_principal_capability_unique (source_id, principal_id, capability),
                CONSTRAINT pc_principal_fk FOREIGN KEY (source_id, principal_id)
                    REFERENCES web_principals (source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT pc_revoked_check CHECK (revoked_at IS NULL OR revoked_at >= granted_at)
                SQL,
            'audit_events' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                source_id BIGINT UNSIGNED NOT NULL,
                actor_principal_id BIGINT UNSIGNED NULL DEFAULT NULL,
                event_type VARCHAR(64) NOT NULL,
                outcome VARCHAR(16) NOT NULL,
                subject_type VARCHAR(32) NULL DEFAULT NULL,
                subject_id BIGINT UNSIGNED NULL DEFAULT NULL,
                request_id BINARY(16) NULL DEFAULT NULL,
                decision_locator VARBINARY(1024) NULL DEFAULT NULL,
                occurred_at DATETIME(6) NOT NULL,
                KEY ae_source_time_index (source_id, occurred_at, id),
                KEY ae_source_actor_time_index (source_id, actor_principal_id, occurred_at),
                CONSTRAINT ae_source_fk FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT ae_actor_fk FOREIGN KEY (source_id, actor_principal_id)
                    REFERENCES web_principals (source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT ae_outcome_check CHECK (outcome IN ('succeeded', 'denied', 'failed')),
                CONSTRAINT ae_subject_check CHECK ((subject_type IS NULL AND subject_id IS NULL)
                    OR (subject_type IS NOT NULL AND subject_id IS NOT NULL))
                SQL,
            'source_publications' => <<<'SQL'
                source_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
                snapshot_id BIGINT UNSIGNED NOT NULL,
                publication_kind VARCHAR(16) NOT NULL,
                evidence_locator VARBINARY(1024) NOT NULL,
                published_at DATETIME(6) NOT NULL,
                UNIQUE KEY sp_source_snapshot_unique (source_id, snapshot_id),
                CONSTRAINT sp_source_fk FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT sp_snapshot_fk FOREIGN KEY (source_id, snapshot_id)
                    REFERENCES snapshots (source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT sp_kind_check CHECK (publication_kind IN ('synthetic', 'validated'))
                SQL,
        ];
        $created = [];
        try {
            foreach ($definitions as $table => $definition) {
                DB::statement("CREATE TABLE `{$table}` ({$definition}) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
                $created[] = $table;
            }
        } catch (Throwable $exception) {
            foreach (array_reverse($created) as $table) {
                DB::statement("DROP TABLE `{$table}`");
            }
            throw $exception;
        }
    }

    public function down(): void
    {
        $this->requireMysql();
        $tables = ['source_publications', 'audit_events', 'principal_capabilities', 'web_principals'];
        // Rollback never erases authentication, grant, publication or audit evidence.
        foreach ($tables as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('Operational foundation rollback requires empty tables; preserve evidence before rollback.');
            }
        }
        foreach ($tables as $table) {
            DB::statement("DROP TABLE `{$table}`");
        }
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('The operational foundation requires MySQL.');
        }
    }
};
