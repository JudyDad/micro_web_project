<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->requireMysql();
        $definitions = [
            'web_accounts' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARBINARY(800) NOT NULL,
                password_hash VARCHAR(255) NULL DEFAULT NULL,
                credential_principal_id BIGINT UNSIGNED NULL DEFAULT NULL,
                disabled_at DATETIME(6) NULL DEFAULT NULL,
                created_at DATETIME(6) NOT NULL,
                updated_at DATETIME(6) NOT NULL,
                UNIQUE KEY wa_username_unique (username),
                KEY wa_credential_index (credential_principal_id),
                CONSTRAINT wa_credential_fk FOREIGN KEY (credential_principal_id) REFERENCES web_principals (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wa_credential_check CHECK (password_hash IS NOT NULL OR credential_principal_id IS NOT NULL)
                SQL,
            'account_legacy_links' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id BIGINT UNSIGNED NOT NULL,
                source_id BIGINT UNSIGNED NOT NULL,
                principal_id BIGINT UNSIGNED NOT NULL,
                decision_locator VARBINARY(1024) NOT NULL,
                reviewed_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY all_principal_unique (principal_id),
                UNIQUE KEY all_account_source_principal_unique (account_id, source_id, principal_id),
                KEY all_source_principal_index (source_id, principal_id),
                CONSTRAINT all_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT all_principal_fk FOREIGN KEY (source_id, principal_id) REFERENCES web_principals (source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT all_revoked_check CHECK (revoked_at IS NULL OR revoked_at >= reviewed_at)
                SQL,
            'account_city_capabilities' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id BIGINT UNSIGNED NOT NULL,
                city_id BIGINT UNSIGNED NOT NULL,
                capability VARCHAR(64) NOT NULL,
                decision_locator VARBINARY(1024) NOT NULL,
                granted_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY acc_account_city_capability_unique (account_id, city_id, capability),
                KEY acc_city_index (city_id),
                CONSTRAINT acc_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT acc_city_fk FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT acc_revoked_check CHECK (revoked_at IS NULL OR revoked_at >= granted_at)
                SQL,
            'account_audit_events' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                actor_account_id BIGINT UNSIGNED NULL DEFAULT NULL,
                subject_account_id BIGINT UNSIGNED NULL DEFAULT NULL,
                city_id BIGINT UNSIGNED NULL DEFAULT NULL,
                event_type VARCHAR(64) NOT NULL,
                outcome VARCHAR(16) NOT NULL,
                subject_type VARCHAR(32) NULL DEFAULT NULL,
                subject_id BIGINT UNSIGNED NULL DEFAULT NULL,
                decision_locator VARBINARY(1024) NULL DEFAULT NULL,
                occurred_at DATETIME(6) NOT NULL,
                KEY aae_actor_time_index (actor_account_id, occurred_at, id),
                KEY aae_subject_time_index (subject_account_id, occurred_at, id),
                KEY aae_city_time_index (city_id, occurred_at, id),
                CONSTRAINT aae_actor_fk FOREIGN KEY (actor_account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT aae_subject_fk FOREIGN KEY (subject_account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT aae_city_fk FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT aae_outcome_check CHECK (outcome IN ('succeeded', 'denied', 'failed')),
                CONSTRAINT aae_subject_check CHECK ((subject_type IS NULL AND subject_id IS NULL) OR (subject_type IS NOT NULL AND subject_id IS NOT NULL))
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
        $tables = ['account_audit_events', 'account_city_capabilities', 'account_legacy_links', 'web_accounts'];
        foreach ($tables as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('Unified account rollback requires empty tables; preserve evidence before rollback.');
            }
        }
        foreach ($tables as $table) {
            DB::statement("DROP TABLE `{$table}`");
        }
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Unified accounts require MySQL.');
        }
    }
};
