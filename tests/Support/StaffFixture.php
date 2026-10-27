<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\WebAccount;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Invented staff-operations data: historical source users, their principals and links to web accounts, tasks and notices. */
final class StaffFixture
{
    public const CAPABILITIES = ['tasks.view', 'tasks.manage', 'notifications.view', 'notifications.send', 'reports.export'];

    /** The desktop PBKDF2 hash of `SyntheticFoundationFixture::PASSWORD`, so a linked identity can really log in. */
    public const LEGACY_PASSWORD_HASH = 'AAECAwQFBgcICQoLDA0OD6rirHkgJUrCARvhwiO9AlEz7NUBCDqkiw29w+5zjOOk';

    private const PREFIX = [1 => 'a', 2 => 'd', 3 => 'h', 4 => 'l', 5 => 'w'];

    /** The preservation `user` tables of the two test sources, principals and reviewed links (SQLite shape). */
    public static function schema(): void
    {
        if (Schema::hasTable('web_principals')) {
            return;
        }
        foreach (self::PREFIX as $prefix) {
            Schema::create('legacy_'.$prefix.'_user', function (Blueprint $table) {
                foreach (['record_id', 'origin_source_id', 'origin_table_id', 'UserID', 'UserRoleID', 'IsVisitor', 'IsME', 'IsCommunication', 'IsCashier'] as $column) {
                    $table->integer($column)->nullable();
                }
                $table->text('UserName')->nullable();
                $table->text('Password')->nullable();
                $table->text('Email')->nullable();
            });
        }
        Schema::create('web_principals', function (Blueprint $table) {
            $table->id();
            foreach (['source_id', 'source_table_id', 'source_record_id'] as $column) {
                $table->integer($column)->nullable();
            }
            foreach (['password_hash', 'disabled_at', 'created_at', 'updated_at'] as $column) {
                $table->text($column)->nullable();
            }
        });
        Schema::create('account_legacy_links', function (Blueprint $table) {
            $table->id();
            foreach (['account_id', 'source_id', 'principal_id'] as $column) {
                $table->integer($column)->nullable();
            }
            foreach (['decision_locator', 'reviewed_at', 'revoked_at'] as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    public static function grant(WebAccount $account, array $cities = [1, 2], array $capabilities = self::CAPABILITIES): void
    {
        foreach ($cities as $city) {
            foreach ($capabilities as $capability) {
                if (! DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', $city)->where('capability', $capability)->exists()) {
                    DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p53', 'granted_at' => now('UTC')]);
                }
            }
        }
    }

    /** A published historical source user; returns its `source_records` id (what operational rows reference). */
    public static function user(int $source, int $userId, string $name, array $flags = []): int
    {
        if (! DB::table('source_publications')->where('source_id', $source)->where('snapshot_id', 10)->exists()) {
            DB::table('source_publications')->insert(['source_id' => $source, 'snapshot_id' => 10, 'publication_kind' => 'synthetic']);
        }
        $tableId = PreservationManifest::tableIds(PreservationManifest::load())['user'];
        $record = DB::table('source_records')->insertGetId(['source_id' => $source, 'table_id' => $tableId, 'snapshot_id' => 10]);
        DB::table('legacy_'.self::PREFIX[$source].'_user')->insert(array_replace([
            'record_id' => $record, 'origin_source_id' => $source, 'origin_table_id' => $tableId, 'UserID' => $userId, 'UserName' => $name,
            'UserRoleID' => 2, 'IsVisitor' => 0, 'IsME' => 0, 'IsCommunication' => 0, 'IsCashier' => 0, 'Password' => self::LEGACY_PASSWORD_HASH, 'Email' => '',
        ], $flags));

        return $record;
    }

    public static function principal(int $source, int $record): int
    {
        return DB::table('web_principals')->insertGetId(['source_id' => $source, 'source_table_id' => 117, 'source_record_id' => $record, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
    }

    /** Reviewed link of a web account to the principal of a source user. */
    public static function link(WebAccount $account, int $source, int $principal): int
    {
        return DB::table('account_legacy_links')->insertGetId(['account_id' => $account->id, 'source_id' => $source, 'principal_id' => $principal, 'decision_locator' => 'synthetic:p53', 'reviewed_at' => now('UTC')]);
    }

    /** Assigns a task to a historical user of the city (the desktop's `task_user`). */
    public static function assign(int $city, int $taskRow, int $userRecord, int $legacyUser): int
    {
        $id = ApplicationFixture::row('task_user', $city, ['c_Task_ID' => 0, 'c_User_ID' => $legacyUser], ['Task_ID' => $taskRow]);
        $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_task_user' && $r['child_columns'] === ['User_ID']);
        DB::table('op_task_user')->where('id', $id)->update([$relation['resolved_column'] => $userRecord, $relation['state_column'] => 'resolved']);

        return $id;
    }

    /** A task of the board in a city (type f or a). */
    public static function task(int $city, int $legacy, string $name, string $type = 'f'): int
    {
        return ApplicationFixture::row('task', $city, ['c_T_ID' => $legacy, 'c_T_Name' => $name, 'c_T_State' => 0, 'c_T_Type' => $type]);
    }

    /** The project's assignment of a task, not done by default. */
    public static function assignTask(int $project, int $taskRow, int $state = 0, int $city = 1): int
    {
        return ApplicationFixture::row('task_microproject', $city, ['c_State' => $state, 'c_Date' => '2026-10-19 00:00:00'], ['MicroProject_ID' => $project, 'Task_ID' => $taskRow]);
    }

    /**
     * An unseen desktop notice (`op_user_notification`) addressed to a historical source user (`$record` = its source_records id, `$userId` = its desktop number).
     *
     * @param  array<string, mixed>  $fields  overrides of the payload columns
     */
    public static function legacyNotice(int $city, int $record, int $userId, string $body, string $date, array $fields = [], ?int $project = null, ?int $sender = null): int
    {
        $id = ApplicationFixture::row('user_notification', $city, $fields + [
            'c_Body' => $body, 'c_Date' => $date, 'c_P_Name' => 'Synthetic beneficiary', 'c_Pay_ID' => 0, 'c_Seen' => 0, 'c_User_ID' => $userId,
            'c_MicroProject_ID' => $project === null ? null : DB::table('op_microproject')->where('id', $project)->value('c_MP_ID'),
        ], $project === null ? [] : ['MicroProject_ID' => $project]);
        $relations = collect(OperationalManifest::load()['relationships'])->filter(fn ($r) => $r['target_table'] === 'op_user_notification');
        $user = $relations->first(fn ($r) => $r['child_columns'] === ['User_ID']);
        $values = [$user['resolved_column'] => $record, $user['state_column'] => 'resolved'];
        if ($sender !== null) {
            $from = $relations->first(fn ($r) => $r['child_columns'] === ['Sender_ID']);
            $values += [$from['resolved_column'] => $sender, $from['state_column'] => 'resolved'];
        }
        DB::table('op_user_notification')->where('id', $id)->update($values);

        return $id;
    }

    /** A web account with the given roles per city (`[city => [role, …]]`); only the role assignment is recorded, no capabilities. */
    public static function account(string $name, array $roles = [], ?string $email = null): WebAccount
    {
        $id = DB::table('web_accounts')->insertGetId(['username' => $name, 'email' => $email === null ? null : mb_strtolower($email), 'password_hash' => 'x', 'password_changed_at' => now('UTC'), 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        $account = WebAccount::findOrFail($id);
        foreach ($roles as $city => $list) {
            foreach ($list as $role) {
                self::holds($account, $city, $role);
            }
        }

        return $account;
    }

    /** Records that an account holds a role in a city (routing only). */
    public static function holds(WebAccount $account, int $city, string $role): void
    {
        DB::table('account_city_roles')->insert(['account_id' => $account->id, 'city_id' => $city, 'role' => $role, 'decision_locator' => 'synthetic:p53', 'assigned_at' => now('UTC')]);
    }

    /** Makes an account responsible for a task of a city's catalog. */
    public static function responsible(WebAccount $account, int $city, int $taskRow): int
    {
        return DB::table('account_city_tasks')->insertGetId(['account_id' => $account->id, 'city_id' => $city, 'source_id' => $city, 'task_id' => $taskRow, 'assigned_at' => now('UTC')]);
    }

    /**
     * A web notice. `$account` null makes it a shared copy.
     *
     * @param  array<string, mixed>  $fields  overrides of the columns
     */
    public static function notice(int $city, ?int $account, string $kind, string $body, string $date, array $fields = [], ?int $project = null): int
    {
        return DB::table('web_notices')->insertGetId($fields + [
            'city_id' => $city, 'source_id' => $city, 'account_id' => $account, 'sender_account_id' => null, 'project_id' => $project,
            'project_number' => $project === null ? null : DB::table('op_microproject')->where('id', $project)->value('c_MP_ID'),
            'kind' => $kind, 'pay_key' => null, 'notice_date' => $date, 'title' => 'Synthetic beneficiary', 'body' => $body, 'seen' => 0, 'created_at' => now('UTC'),
        ]);
    }
}
