<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Applications\ApplicationService;
use App\Services\Assessments\ChecklistService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/** Opt-in writes against the actual approved schema on an empty disposable MySQL target. */
final class ApplicationOperationalWorkflowTest extends TestCase
{
    public function test_application_writes_use_the_approved_mysql_schema_and_leave_no_fixture_rows(): void
    {
        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Requires the isolated MySQL test server.');
        }
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
        config([
            'database.default' => 'mysql', 'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '33072',
            'database.connections.mysql.database' => 'micro_projects_test',
            'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '',
            'database.connections.mysql.unix_socket' => '',
        ]);
        DB::purge('mysql');
        self::assertTrue(app()->environment('testing'));
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertStringNotContainsString('MariaDB', DB::selectOne('SELECT VERSION() AS v')->v);
        $existing = array_column(DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()'), 'name');
        self::assertSame([], array_values(array_diff($existing, ['migrations'])), 'Refusing a pre-existing application schema.');
        if ($existing !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        DB::beginTransaction();
        try {
            $account = ApplicationFixture::account();
            $fixture = ApplicationFixture::intake(1);
            $applications = app(ApplicationService::class);
            $id = $applications->create($account, 1, ['c_MP_Name' => 'طلب تجريبي', 'c_MP_DateOfRequest' => '2026-10-20'], $fixture['person'], $fixture['references']);
            $applications->update($account, $id, ['c_MP_Note' => '  exact  '], $applications->find($account, $id)['version']);
            foreach (['material' => ['c_Name' => 'مادة', 'c_Amount' => '2', 'c_Price' => '3.125'], 'rehab' => ['c_PropertyArea' => '001 square units']] as $kind => $fields) {
                $applications->saveDetail($account, $id, $kind, $fields, $applications->find($account, $id)['version']);
            }
            $question = ApplicationFixture::row('agquestion', 1, ['c_ID' => 1, 'c_Name' => 'سؤال']);
            $answer = ApplicationFixture::row('aganswer', 1, ['c_ID' => 1, 'c_Name' => 'جواب'], ['Question_ID' => $question]);
            $applications->saveDetail($account, $id, 'agriculture', [], $applications->find($account, $id)['version'], null, ['Question_ID' => $question, 'Answer_ID' => $answer]);
            $data = $applications->find($account, $id);
            self::assertSame('  exact  ', $data['application']->c_MP_Note);
            self::assertSame('zero', $data['application']->c_ApplicationState);
            self::assertSame(1000, (int) $data['application']->c_MP_ID);
            self::assertCount(1, $data['material']);
            self::assertCount(1, $data['rehab']);
            self::assertCount(1, $data['agriculture']);
            self::assertSame('web', DB::table('operational_entities')->where('id', $id)->value('identity_kind'));
            self::assertSame(0, DB::table('operational_origins')->count());
            self::assertSame(0, DB::table('source_records')->count());
            self::assertGreaterThan(0, DB::table('account_audit_events')->where('event_type', 'applications.updated')->count());

            // P4.3: lifecycle handoff, state history, tasks and the assessment checklist on the real constraints.
            $lifecycle = app(LifecycleService::class);
            self::assertSame('On Hold', $lifecycle->panel($account, $id)['current']->state_name_en);
            $references = [];
            foreach (['MP_FundType_ID', 'MP_PlaceType_ID'] as $key) {
                $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'microproject' && $r['child_columns'] === [$key]);
                $references[$key] = ApplicationFixture::row($relation['parent_source_table'], 1, ['c_ID' => 70]);
            }
            $applications->update($account, $id, ['c_MP_RequestedAmount' => '1500.5'] + array_combine(array_map(fn ($n) => 'c_MP_Agreement'.$n, range(1, 5)), array_fill(0, 5, '1')), $applications->find($account, $id)['version'], $references);
            $applications->convertToFull($account, $id, $applications->find($account, $id)['version']);
            self::assertSame('project', DB::table('op_microproject')->where('id', $id)->value('c_ApplicationState'));
            $panel = $lifecycle->panel($account, $id);
            self::assertSame('Waiting', $panel['current']->state_name_en);
            self::assertSame(2, DB::table('op_task_microproject')->count());
            $lifecycle->transition($account, $id, $fixture['states']['Accepted'], $fixture['reason'], 'ملاحظة', $panel['version']);
            self::assertSame(4, DB::table('op_task_microproject')->count());
            self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
            self::assertSame(3, DB::table('op_microproject_state')->count());
            $members = ApplicationFixture::checklistCatalog(1);
            $checklists = app(ChecklistService::class);
            $checklists->save($account, $id, $fixture['person'], null, ['c_Notes' => '  تقييم  ', 'c_HasHomeVisit' => '1', 'c_HVisit_Date' => '2026-10-21'], ['HVisit_Visitor1_ID' => $members['t']],
                [['location' => 't', 'member_id' => $members['t'], 'opinion' => '1', 'notes' => 'ملاحظة']], $checklists->show($account, $id, $fixture['person'])['version']);
            $assessment = $checklists->show($account, $id, $fixture['person']);
            self::assertSame('  تقييم  ', $assessment['header']->c_Notes);
            self::assertCount(1, $assessment['members']);
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        }
        self::assertSame(0, DB::table('migrations')->count());
    }
}
