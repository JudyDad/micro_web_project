<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Field\MonitoringVisitService;
use App\Services\Field\ProfessionMeetingService;
use App\Services\Field\RehabVisitService;
use App\Services\Field\SpecialCaseService;
use App\Services\Field\VisitScheduleService;
use App\Services\Field\VisitService;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** Opt-in field-operations writes (P5.2) and the deletion table against the actual approved schema on an empty disposable MySQL target. */
final class FieldOperationalWorkflowTest extends TestCase
{
    public function test_field_writes_and_the_deletion_table_work_on_the_approved_mysql_schema(): void
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
        self::assertSame(1, DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::selectOne('SELECT DATABASE() AS db')->db)->where('TABLE_NAME', 'operational_deletions')->count());
        $this->assertRollbackIsRefusedWhileDeletionEvidenceExists();
        DB::beginTransaction();
        try {
            $account = ApplicationFixture::account();
            FieldFixture::grant($account, [1]);
            $intake = ApplicationFixture::intake(1);
            FieldFixture::specialTypes(1);
            $visitCatalog = FieldFixture::visitCatalog(1);
            $me = FieldFixture::meCatalog(1);
            $meetingCatalog = FieldFixture::meetingCatalog(1);
            $project = FinanceFixture::acceptedProject($account, $intake);
            FieldFixture::assignVisitTasks($project, ['tasks' => $visitCatalog['tasks'] + $me['tasks']]);
            $tables = app(OperationalTables::class);

            // Special case: exact text, edit in place, delete of a web record.
            $cases = app(SpecialCaseService::class);
            $type = (int) DB::table('op_specialcasetype')->orderBy('id')->value('id');
            $caseId = $cases->save($account, $project, ['type_id' => $type, 'date' => '2026-11-01', 'reason' => 'سبب', 'description' => ' exact  text ', 'action' => ''], $cases->show($account, $project)['version']);
            self::assertSame(' exact  text ', DB::table('op_specialcase')->where('id', $caseId)->value('c_Description'));
            self::assertSame('web', DB::table('operational_entities')->where('id', $caseId)->value('identity_kind'));

            // Opening visit: real column types, office row and task.
            $visits = app(VisitService::class);
            $input = ['date' => '2026-11-02', 'street_id' => $visitCatalog['street'], 'place_note' => 'قرب السوق', 'q1_value' => '1', 'q1_text' => 'ok', 'q4_value' => '3', 'office1_value' => '2', 'other_comments' => 'x', 'indicators' => 'y'];
            $visitId = $visits->save($account, $project, 'O-op', $input, $visits->hub($account, $project)['version']);
            $visit = DB::table('op_visit')->where('id', $visitId)->first();
            self::assertSame('O-op', $visit->c_Kind);
            self::assertSame('2026-11-02', substr((string) $visit->c_Date, 0, 10));
            self::assertSame(3, (int) $visit->c_Ans4_Value);
            self::assertSame(1, DB::table('op_office_monitoring')->count());
            self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $visitCatalog['tasks'][25])->value('c_State'));
            self::assertSame('قرب السوق', DB::table('op_microproject')->where('id', $project)->value('c_MP_AddressAfterFund'));

            // Closing visit finishes the project once.
            $visits->save($account, $project, 'O-cl', ['date' => '2026-12-01', 'q1_value' => '0', 'q2_value' => '3', 'q3_value' => '2', 'q5_value' => '3'], $visits->hub($account, $project)['version']);
            self::assertSame('Finished', app(LifecycleService::class)->panel($account, $project)['current']->state_name_en);

            // M&E visit: double profit, varchar(2) number, answers and result.
            $monitoring = app(MonitoringVisitService::class);
            [$q1, $q2] = $me['questions'];
            $meId = $monitoring->save($account, $project, $intake['person'], null, [
                'number' => '1', 'date' => '2026-11-03', 'profit' => '1,250.5', 'ans1' => 'summary',
                'a'.$q1['id'] => $q1['answers']['Yes'], 'a'.$q2['id'] => $q2['answers']['No'],
            ], $monitoring->hub($account, $project)['version']);
            $meVisit = DB::table('op_mevisit')->where('id', $meId)->first();
            self::assertEqualsWithDelta(1250.5, (float) $meVisit->c_Profit, 0.0);
            self::assertSame('1', $meVisit->c_Number);
            self::assertSame(5 + 1 + 4, (int) $meVisit->c_Result);
            self::assertSame(2, DB::table('op_mevisit_answer')->count());

            // Profession meeting: every detail table.
            $meetings = app(ProfessionMeetingService::class);
            $o1 = $meetingCatalog['options']['1'];
            $o4 = $meetingCatalog['options']['4'];
            $q = $meetingCatalog['questions'];
            $meetingInput = [
                'career_id' => $intake['references']['Career_ID'], 'date' => '2026-11-10', 'place' => 'قاعة', 'invited' => '10', 'attendance' => '4',
                'oc' => [$o1[1] => '3', $o1[2] => '1'], 'os' => [$o4[1] => '1', $o4[3] => '1'], 'ot' => [$o4[3] => 'سياح'], 'tx' => [$q['5'] => 'إصلاحات'],
                'cc' => [1 => '1', 2 => '3', 3 => '0'], 'cr' => [1 => 'a', 2 => 'b'],
                'season' => [['count' => '4', 'from' => 'أيار', 'to' => 'تموز', 'reason' => 'ذروة']], 'brands' => "A\nB",
                'equipment' => [['spec' => 'لحام', 'items' => 'ماكينة']], 'challenge' => [['name' => 'اقتصاد', 'count' => '4', 'solution' => 'قروض']],
                'part' => [$q['19'] => [['name' => 'مدرب', 'speciality' => 'لحام']]],
                'attendees' => [['name' => 'a'], ['name' => 'b'], ['name' => 'c'], ['name' => 'd']],
                'minutes' => [$meetingCatalog['sections'][1] => 'x', $meetingCatalog['sections'][2] => 'y'],
            ];
            $meetingId = $meetings->save($account, null, 1, $meetingInput, $meetings->form($account, null, 1)['version']);
            self::assertSame([6], [(int) DB::table('op_profession_meeting')->value('c_AbsenceCount')]);
            self::assertSame(4, DB::table('op_profession_meeting_attendee')->count());
            $meetings->save($account, $meetingId, null, ['place' => 'قاعة جديدة', 'oc' => [$o1[1] => '4']] + $meetingInput, $meetings->form($account, $meetingId, null)['version']);
            self::assertSame('قاعة جديدة', DB::table('op_profession_meeting')->value('c_Place'));

            // Deleting web records removes rows and entities (FK order respected).
            $meetings->delete($account, $meetingId, $meetings->form($account, $meetingId, null)['version']);
            self::assertSame(0, DB::table('op_profession_meeting')->count());
            self::assertSame(0, DB::table('op_profession_meeting_option_answer')->count());
            $monitoring->delete($account, $project, $meId, $monitoring->hub($account, $project)['version']);
            self::assertSame(0, DB::table('op_mevisit_answer')->count());
            $visits->delete($account, $project, 'O-cl', $visits->hub($account, $project)['version']);
            $cases->delete($account, $project, $caseId, $cases->show($account, $project)['version']);
            self::assertSame(0, DB::table('op_specialcase')->count());

            // operational_deletions on the real schema: hides reads, unique per entity, entity protected, restorable.
            $keep = $cases->save($account, $project, ['type_id' => $type, 'date' => '2026-11-05', 'reason' => 'kept'], $cases->show($account, $project)['version']);
            $entity = DB::table('operational_entities')->where('id', $keep)->first();
            $row = [
                'city_id' => $entity->city_id, 'source_id' => $entity->source_id, 'source_table_id' => $entity->source_table_id, 'entity_id' => $keep,
                'deleted_by_account_id' => $account->id, 'deleted_at' => now('UTC')->format('Y-m-d H:i:s.u'), 'reason' => 'ملاحظة',
            ];
            DB::table('operational_deletions')->insert($row);
            self::assertSame(0, $tables->query('specialcase')->where('p.id', $keep)->count());
            self::assertSame(1, $tables->query('specialcase', true)->where('p.id', $keep)->count());
            self::assertSame(0, $cases->show($account, $project)['cases']->count());
            $this->mustReject(fn () => DB::table('operational_deletions')->insert($row));
            $this->mustReject(fn () => DB::table('operational_deletions')->insert(['entity_id' => 999999] + $row));
            $this->mustReject(fn () => DB::table('operational_deletions')->insert(['deleted_by_account_id' => 999999, 'entity_id' => 888888] + $row));
            $this->mustReject(fn () => DB::table('operational_entities')->where('id', $keep)->delete());
            $tables->unhide('specialcase', $keep);
            self::assertSame(1, $cases->show($account, $project)['cases']->count());

            // The schedule's raw SQL (MONTH(), joins) runs on MySQL.
            StaffFixture::notice(1, null, 'visit', 'Visit Opening-O', '2026-11-05', [], $project);
            self::assertSame(1, app(VisitScheduleService::class)->schedule($account, ['months' => [11], 'year' => 2026])['rows']->total());

            // Rehabilitation closing visit.
            DB::table('op_microprojecttype')->where('id', $intake['references']['MP_Type_ID'])->update(['c_Name' => 'ترميم منزل']);
            $fund = ApplicationFixture::row('fundtype', 1, ['c_ID' => 3, 'c_Name' => 'منحة', 'c_Name_en' => 'Grant']);
            DB::table('op_microproject')->where('id', $project)->update($tables->link('microproject', 'MP_FundType_ID', $fund));
            $rehab = app(RehabVisitService::class);
            $rehabInput = ['date' => '2026-12-05'];
            foreach (RehabVisitService::QUESTIONS as $n => $kind) {
                $rehabInput['q'.$n.'_value'] = $kind === 'yes_no' ? '1' : '2';
            }
            $rehab->save($account, $project, $rehabInput, $rehab->form($account, $project)['version']);
            self::assertSame(1, DB::table('op_rehabvisit')->count());
            self::assertSame(2, (int) DB::table('op_rehabvisit')->value('c_Ans1_Value'));

        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            self::assertSame(0, Artisan::call('migrate:reset', ['--force' => true]), Artisan::output()); // all batches: the guard helper re-ran one
        }
        self::assertSame(0, DB::table('migrations')->count());
    }

    /** The migration refuses to drop the table while a deletion record exists; committed data is removed afterwards. */
    private function assertRollbackIsRefusedWhileDeletionEvidenceExists(): void
    {
        $account = ApplicationFixture::account();
        $tables = app(OperationalTables::class);
        $id = $tables->insert('specialcase', 1, $tables->source(1), ['c_Reason' => 'guard']);
        $entity = DB::table('operational_entities')->where('id', $id)->first();
        DB::table('operational_deletions')->insert([
            'city_id' => $entity->city_id, 'source_id' => $entity->source_id, 'source_table_id' => $entity->source_table_id, 'entity_id' => $id,
            'deleted_by_account_id' => $account->id, 'deleted_at' => now('UTC')->format('Y-m-d H:i:s.u'),
        ]);
        try {
            // The staff-model migration (000009) sits on top; its own guard must pass first, then 000008 refuses.
            DB::table('web_accounts')->update(['password_changed_at' => null]);
            Artisan::call('migrate:rollback', ['--force' => true, '--step' => 2]);
            self::fail('The rollback must be refused while deletion records exist.');
        } catch (\RuntimeException $exception) {
            self::assertStringContainsString('Deletion records', $exception->getMessage());
        } finally {
            DB::table('operational_deletions')->delete();
            DB::table('op_specialcase')->where('id', $id)->delete();
            DB::table('operational_entities')->where('id', $id)->delete();
            DB::table('account_city_capabilities')->where('account_id', $account->id)->delete();
            DB::table('web_accounts')->where('id', $account->id)->delete();
            DB::table('project_number_sequences')->delete();
            Artisan::call('migrate', ['--force' => true]); // restore the staff-model migration the attempted rollback removed
        }
        self::assertSame(1, DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::selectOne('SELECT DATABASE() AS db')->db)->where('TABLE_NAME', 'operational_deletions')->count());
    }

    private function mustReject(callable $call): void
    {
        try {
            $call();
        } catch (QueryException|\RuntimeException) {
            return;
        }
        self::fail('The database must reject this write.');
    }
}
