<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Applications\ApplicationService;
use App\Services\Assessments\ChecklistService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FullApplicationFixture;
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

            // 27 Oct one-form intake on the real constraints: new applicant, family book, membership and project in one transaction.
            $catalog = [
                'P_MaritalStatus_ID' => ApplicationFixture::row('maritalstatus', 1, ['c_ID' => 1, 'c_Name' => 'متزوج']),
                'P_Parish_ID' => ApplicationFixture::row('parish', 1, ['c_ID' => 1, 'c_Name_ar' => 'اللاتين', 'c_Name_en' => 'Latin']),
                'Street_ID' => ApplicationFixture::row('street', 1, ['c_ID' => 1, 'c_Name' => 'الحميدية']),
                'P_HomeType_ID' => ApplicationFixture::row('assettype', 1, ['c_ID' => 1, 'c_Name' => 'ملك']),
            ];
            $relation = ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
            $applicant = ['c_P_FirstName' => 'سامر', 'c_P_LastName' => 'حداد', 'c_P_Sex' => 'ذكر', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'مريم', 'c_P_NationalNumber' => '01234567890',
                'c_P_DOB' => '1988-05-01', 'c_P_IntermidiarySide' => 'رعية اللاتين', 'c_P_HomeAddress' => 'شارع الكنيسة 5', 'c_P_Mobile' => '0999000000', 'c_P_FullName_en' => null];
            $second = $applications->intake($account, 1, ['c_MP_Name' => 'مشروع خياطة', 'c_MP_DateOfRequest' => '2026-10-27'], $fixture['references'],
                ['id' => null, 'fields' => $applicant, 'references' => $catalog, 'confirm_duplicate' => false], ['id' => null, 'number' => 'F-100', 'confirm_duplicate' => false]);
            self::assertSame(1001, (int) DB::table('op_microproject')->where('id', $second)->value('c_MP_ID'));
            $newPerson = DB::table('op_person')->where('c_P_NationalNumber', '01234567890')->first();
            self::assertNotNull($newPerson);
            self::assertSame('web', DB::table('operational_entities')->where('id', $newPerson->id)->value('identity_kind'));
            self::assertSame('F-100', DB::table('op_family')->value('c_F_Number'));
            $membership = DB::table('op_person_family')->first();
            $relationColumn = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'person_family' && $r['child_columns'] === ['Relation_ID'])['resolved_column'];
            self::assertSame($relation, (int) $membership->{$relationColumn});
            self::assertCount(1, $applications->printable($account, $second)['applicants'][0]['households']);
            try {
                $applications->intake($account, 1, ['c_MP_Name' => 'مكرر', 'c_MP_DateOfRequest' => '2026-10-27'], $fixture['references'],
                    ['id' => null, 'fields' => $applicant, 'references' => $catalog, 'confirm_duplicate' => false], ['id' => null, 'number' => 'F-100', 'confirm_duplicate' => false]);
                self::fail('Duplicate national and family numbers must need explicit confirmation.');
            } catch (ValidationException $e) {
                self::assertSame(['person.duplicate', 'family.duplicate'], array_keys($e->errors()));
            }
            self::assertSame(2, DB::table('op_microproject')->count());
            $stateRef = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'microproject_state' && $r['child_columns'] === ['MicroProject_ID'])['resolved_column'];

            // P4.3: lifecycle handoff, state history, tasks and the assessment checklist on the real constraints.
            $lifecycle = app(LifecycleService::class);
            self::assertSame('On Hold', $lifecycle->panel($account, $id)['current']->state_name_en);
            FullApplicationFixture::complete($id, $fixture['person'], $fixture);
            $applications->convertToFull($account, $id, $applications->find($account, $id)['version']);
            self::assertSame('project', DB::table('op_microproject')->where('id', $id)->value('c_ApplicationState'));
            $panel = $lifecycle->panel($account, $id);
            self::assertSame('Waiting', $panel['current']->state_name_en);
            self::assertSame(2, DB::table('op_task_microproject')->count());
            $lifecycle->transition($account, $id, $fixture['states']['Accepted'], $fixture['reason'], 'ملاحظة', $panel['version']);
            self::assertSame(4, DB::table('op_task_microproject')->count());
            self::assertSame(1, DB::table('op_microproject_state')->where($stateRef, $id)->where('c_Current_State', 1)->count());
            self::assertSame(3, DB::table('op_microproject_state')->where($stateRef, $id)->count());
            self::assertSame(1, DB::table('op_microproject_state')->where($stateRef, $second)->count(), 'The one-form intake records the desktop On Hold row once.');
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
