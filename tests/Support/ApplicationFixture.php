<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\WebAccount;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/../../database/schema/OperationalManifest.php';

/** Invented data only. The SQLite schema tests behavior, never MySQL DDL or locking. */
final class ApplicationFixture
{
    public static function intake(int $city): array
    {
        $person = self::row('person', $city, ['c_P_FirstName' => 'Synthetic', 'c_P_LastName' => 'Applicant', 'c_P_Sex' => 'Unknown']);
        $category = self::row('category', $city, ['c_ID' => 10, 'c_Name' => 'Synthetic category']);
        $subcategory = self::row('subcategory', $city, ['c_ID' => 11, 'c_Name' => 'Synthetic subcategory'], ['Category_ID' => $category]);
        $career = self::row('career', $city, ['c_ID' => 12, 'c_Name' => 'Synthetic career'], ['SubCategory_ID' => $subcategory]);
        $type = self::row('microprojecttype', $city, ['c_ID' => 13, 'c_Name' => 'Synthetic type']);
        $subtype = self::row('microprojectsubtype', $city, ['c_ID' => 14, 'c_Name' => 'Synthetic subtype'], ['Type_ID' => $type]);

        $states = [];
        foreach ([[0, 'مرفوض', 'Rejected'], [1, 'مقبول', 'Accepted'], [2, 'مؤجل', 'Delayed'], [3, 'بالانتظار', 'Waiting'], [4, 'ممول', 'Financed'], [5, 'منتهي', 'Finished'], [6, 'منسحب', 'Withdrew'], [7, 'ملغى', 'Canceled'], [8, 'مغلق', 'Closed'], [9, 'قيد الدراسة', 'Under Review'], [10, 'تسجيل أولي', 'On Hold']] as [$raw, $ar, $en]) {
            $states[$en] = self::row('state', $city, ['c_ID' => $raw, 'c_Name_ar' => $ar, 'c_Name_en' => $en]);
        }
        $tasks = [];
        foreach ([[1, 'a'], [2, 'a'], [3, 'f'], [4, 'f']] as [$raw, $taskType]) {
            $tasks[$raw] = self::row('task', $city, ['c_T_ID' => $raw, 'c_T_Name' => 'Synthetic task '.$raw, 'c_T_State' => 0, 'c_T_Type' => $taskType]);
        }
        $reason = self::row('statereason', $city, ['c_ID' => 1, 'c_Name' => 'Synthetic reason']);

        return ['person' => $person, 'states' => $states, 'tasks' => $tasks, 'reason' => $reason, 'references' => ['SubCategory_ID' => $subcategory, 'Career_ID' => $career, 'MP_Type_ID' => $type, 'MP_SubType_ID' => $subtype]];
    }

    /** Checklist catalog: members by location (k, t, c). */
    public static function checklistCatalog(int $city): array
    {
        $members = [];
        foreach (['k' => 'Key Person', 't' => 'M&E Team', 'c' => 'Program Manager'] as $type => $name) {
            $position = self::row('checklist_position', $city, ['c_ID' => ord($type), 'c_Name' => $name, 'c_Type' => $type]);
            $members[$type] = self::row('checklist_member', $city, ['c_ID' => ord($type), 'c_Name' => 'Synthetic '.$type, 'c_IsActive' => 1], ['Position_ID' => $position]);
        }
        $members['t2'] = self::row('checklist_member', $city, ['c_ID' => 200, 'c_Name' => 'Synthetic team two', 'c_IsActive' => 1], ['Position_ID' => DB::table('op_checklist_member')->where('id', $members['t'])->value('ref_449348f6780e_id')]);

        return $members;
    }

    public static function sqliteSchema(): void
    {
        $support = [
            'web_accounts' => ['username', 'email', 'password_hash', 'password_changed_at', 'credential_principal_id', 'disabled_at', 'created_at', 'updated_at'],
            'account_city_roles' => ['account_id', 'city_id', 'role', 'decision_locator', 'assigned_at', 'revoked_at'],
            'account_city_tasks' => ['account_id', 'city_id', 'source_id', 'task_id', 'assigned_at', 'revoked_at'],
            'account_password_resets' => ['account_id', 'token_sha256', 'requested_at', 'expires_at', 'used_at'],
            'web_notices' => ['city_id', 'source_id', 'account_id', 'sender_account_id', 'project_id', 'project_number', 'kind', 'pay_key', 'notice_date', 'original_date', 'title', 'body', 'note', 'change_reason', 'seen', 'created_at'],
            'cities' => ['name_en', 'name_ar'], 'sources' => ['city_id', 'name'], 'source_tables' => ['name'],
            'account_city_capabilities' => ['account_id', 'city_id', 'capability', 'revoked_at', 'decision_locator', 'granted_at'],
            'account_audit_events' => ['actor_account_id', 'subject_account_id', 'city_id', 'event_type', 'outcome', 'subject_type', 'subject_id', 'decision_locator', 'occurred_at'],
            'source_records' => ['source_id', 'table_id', 'snapshot_id'],
            'source_publications' => ['source_id', 'snapshot_id', 'publication_kind'],
            'project_number_sequences' => ['source_id', 'next_number'],
            'operational_deletions' => ['city_id', 'source_id', 'source_table_id', 'entity_id', 'deleted_by_account_id', 'deleted_at', 'reason'],
        ];
        foreach ($support as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns) {
                $table->id();
                foreach ($columns as $column) {
                    str_ends_with($column, '_id') || in_array($column, ['next_number', 'seen', 'pay_key', 'project_number'], true) ? $table->integer($column)->nullable() : $table->text($column)->nullable();
                }
            });
        }
        foreach (OperationalManifest::tables() as $definition) {
            if (! str_starts_with($definition['name'], 'op_') && ! in_array($definition['name'], ['operational_entities', 'operational_origins'], true)) {
                continue;
            }
            Schema::create($definition['name'], function (Blueprint $table) use ($definition) {
                foreach ($definition['columns'] as $column) {
                    $name = $column['name'];
                    if ($name === 'id') {
                        $table->id();

                        continue;
                    }
                    $field = preg_match('/^(?:BIGINT|int|tinyint)/i', $column['definition']) ? $table->integer($name) : $table->text($name);
                    $field->nullable();
                    if (preg_match("/DEFAULT '([^']*)'/", $column['definition'], $match)) {
                        $field->default($match[1]);
                    }
                }
            });
        }
        foreach ([1 => 'Aleppo', 2 => 'Damascus'] as $id => $name) {
            DB::table('cities')->insert(['id' => $id, 'name_en' => $name, 'name_ar' => 'مدينة تجريبية']);
            DB::table('sources')->insert(['id' => $id, 'city_id' => $id, 'name' => $name]);
        }
        foreach (PreservationManifest::tableIds(PreservationManifest::load()) as $name => $id) {
            DB::table('source_tables')->insert(['id' => $id, 'name' => $name]);
        }
        StaffFixture::schema();
    }

    public static function account(): WebAccount
    {
        $id = DB::table('web_accounts')->insertGetId(['username' => 'synthetic.applications', 'password_hash' => Hash::make('Synthetic-P42-Only!'), 'password_changed_at' => now('UTC'), 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        foreach ([1, 2] as $city) {
            foreach (['people.view', 'people.manage', 'applications.view', 'applications.manage', 'lifecycle.manage', 'lifecycle.accept', 'assessments.view', 'assessments.manage'] as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p42', 'granted_at' => now('UTC')]);
            }
            DB::table('project_number_sequences')->updateOrInsert(['source_id' => $city], ['next_number' => 1000]);
        }

        return WebAccount::findOrFail($id);
    }

    public static function row(string $kind, int $city, array $fields = [], array $references = []): int
    {
        $table = OperationalManifest::table($kind);
        $tableId = PreservationManifest::tableIds(PreservationManifest::load())[$kind];
        $scope = ['city_id' => $city, 'source_id' => $city, 'source_table_id' => $tableId];
        $id = DB::table('operational_entities')->insertGetId($scope + ['identity_kind' => 'web', 'created_at' => now('UTC')]);
        $payload = $scope + ['id' => $id] + $fields;
        foreach ($references as $column => $target) {
            $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === $table['name'] && $r['child_columns'] === [$column]);
            if ($relation === null) {
                throw new \RuntimeException('Unknown fixture relationship '.$kind.'.'.$column);
            }
            $payload[$relation['resolved_column']] = $target;
            $payload[$relation['state_column']] = 'resolved';
        }
        DB::table($table['name'])->insert($payload);

        return $id;
    }
}
