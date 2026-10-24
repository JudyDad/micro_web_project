<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Approved by the owner on 24 October 2026 (docs/planning/phase-5/staff-model-proposal.md): the staff model of the single database.
 * Roles, task responsibilities, notices and password resets are web data; the preserved desktop `user` tables are no longer needed
 * at runtime once their data has been adopted.
 */
return new class extends Migration
{
    private const TABLES = ['web_notices', 'account_password_resets', 'account_city_tasks', 'account_city_roles'];

    public function up(): void
    {
        $this->requireMysql();
        DB::statement('ALTER TABLE `web_accounts` ADD COLUMN email VARCHAR(190) NULL DEFAULT NULL AFTER username, ADD COLUMN password_changed_at DATETIME(6) NULL DEFAULT NULL AFTER password_hash, ADD UNIQUE KEY wa_email_unique (email)');
        $definitions = [
            'account_city_roles' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id BIGINT UNSIGNED NOT NULL,
                city_id BIGINT UNSIGNED NOT NULL,
                role VARCHAR(32) NOT NULL,
                decision_locator VARBINARY(1024) NOT NULL,
                assigned_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY acr_account_city_role_unique (account_id, city_id, role),
                KEY acr_city_role_index (city_id, role, revoked_at),
                CONSTRAINT acr_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT acr_city_fk FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT acr_revoked_check CHECK (revoked_at IS NULL OR revoked_at >= assigned_at)
                SQL,
            'account_city_tasks' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id BIGINT UNSIGNED NOT NULL,
                city_id BIGINT UNSIGNED NOT NULL,
                source_id BIGINT UNSIGNED NOT NULL,
                task_id BIGINT UNSIGNED NOT NULL,
                assigned_at DATETIME(6) NOT NULL,
                revoked_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY act_account_task_unique (account_id, task_id),
                KEY act_task_index (city_id, source_id, task_id),
                CONSTRAINT act_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT act_task_fk FOREIGN KEY (city_id, source_id, task_id) REFERENCES op_task (city_id, source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT act_revoked_check CHECK (revoked_at IS NULL OR revoked_at >= assigned_at)
                SQL,
            'account_password_resets' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_id BIGINT UNSIGNED NOT NULL,
                token_sha256 BINARY(32) NOT NULL,
                requested_at DATETIME(6) NOT NULL,
                expires_at DATETIME(6) NOT NULL,
                used_at DATETIME(6) NULL DEFAULT NULL,
                UNIQUE KEY apr_token_unique (token_sha256),
                KEY apr_account_index (account_id, requested_at),
                CONSTRAINT apr_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT apr_expiry_check CHECK (expires_at > requested_at)
                SQL,
            'web_notices' => <<<'SQL'
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                city_id BIGINT UNSIGNED NOT NULL,
                source_id BIGINT UNSIGNED NOT NULL,
                account_id BIGINT UNSIGNED NULL DEFAULT NULL,
                sender_account_id BIGINT UNSIGNED NULL DEFAULT NULL,
                project_id BIGINT UNSIGNED NULL DEFAULT NULL,
                project_number INT NULL DEFAULT NULL,
                kind VARCHAR(32) NOT NULL,
                pay_key BIGINT NULL DEFAULT NULL,
                notice_date DATE NOT NULL,
                original_date DATE NULL DEFAULT NULL,
                title VARCHAR(255) NOT NULL DEFAULT '',
                body MEDIUMTEXT NOT NULL,
                note TEXT NULL DEFAULT NULL,
                change_reason TEXT NULL DEFAULT NULL,
                seen TINYINT NOT NULL DEFAULT 0,
                created_at DATETIME(6) NOT NULL,
                KEY wn_account_index (account_id, seen, notice_date),
                KEY wn_project_index (city_id, project_id, kind),
                KEY wn_pay_index (city_id, pay_key),
                KEY wn_sender_index (sender_account_id),
                CONSTRAINT wn_city_fk FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wn_account_fk FOREIGN KEY (account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wn_sender_fk FOREIGN KEY (sender_account_id) REFERENCES web_accounts (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wn_project_fk FOREIGN KEY (city_id, source_id, project_id) REFERENCES op_microproject (city_id, source_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT wn_kind_check CHECK (kind IN ('visit', 'clearance', 'payment', 'trophy', 'executive_file', 'state', 'purchases', 'message')),
                CONSTRAINT wn_seen_check CHECK (seen IN (0, 1)),
                CONSTRAINT wn_recipient_check CHECK (account_id IS NOT NULL OR kind IN ('visit', 'clearance'))
                SQL,
        ];
        foreach ($definitions as $table => $definition) {
            DB::statement("CREATE TABLE `{$table}` ({$definition}) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
        }
    }

    public function down(): void
    {
        $this->requireMysql();
        foreach (self::TABLES as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('Staff model rollback requires empty tables; preserve evidence before rollback.');
            }
        }
        if (DB::table('web_accounts')->whereNotNull('email')->orWhereNotNull('password_changed_at')->exists()) {
            throw new RuntimeException('Account e-mail and password-change evidence must be preserved before rollback.');
        }
        foreach (self::TABLES as $table) {
            DB::statement("DROP TABLE `{$table}`");
        }
        DB::statement('ALTER TABLE `web_accounts` DROP INDEX wa_email_unique, DROP COLUMN email, DROP COLUMN password_changed_at');
    }

    private function requireMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('The staff model requires MySQL.');
        }
    }
};
