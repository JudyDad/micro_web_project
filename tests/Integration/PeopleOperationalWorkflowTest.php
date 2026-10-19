<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\WebAccount;
use App\Services\People\PeopleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Opt-in writes against the actual approved schema on an empty disposable MySQL target. */
final class PeopleOperationalWorkflowTest extends TestCase
{
    public function test_people_writes_use_the_approved_mysql_schema_and_leave_no_fixture_rows(): void
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
            $accountId = DB::table('web_accounts')->insertGetId(['username' => 'synthetic.p4', 'password_hash' => Hash::make('Synthetic-P4-Only!'), 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
            foreach ([1, 2] as $city) {
                foreach (['people.view', 'people.manage'] as $capability) {
                    DB::table('account_city_capabilities')->insert(['account_id' => $accountId, 'city_id' => $city,
                        'capability' => $capability, 'decision_locator' => 'synthetic:p4', 'granted_at' => now('UTC')]);
                }
            }
            $account = WebAccount::findOrFail($accountId);
            $people = app(PeopleService::class);
            $id = $people->create($account, 1, ['c_P_FirstName' => 'شخص تجريبي', 'c_P_LastName' => 'اختبار', 'c_P_Sex' => 'Unknown', 'c_P_Mobile' => ' 0001 ']);
            $family = $people->createFamily($account, 1, ['c_F_Number' => '000-family']);
            $people->attachFamily($account, $id, $family, ['c_Work_Name' => 'عمل'], $people->find($account, $id)['version']);
            foreach (['education' => [], 'course' => ['c_Name' => 'دورة', 'c_Date' => 'ربيع 2020'], 'workexp' => ['c_W_Status' => 'NO', 'c_W_BeginDate' => '2001']] as $kind => $fields) {
                $people->saveDetail($account, $id, $kind, $fields, $people->find($account, $id)['version']);
            }
            $data = $people->find($account, $id);
            self::assertCount(1, $data['families']);
            self::assertCount(1, $data['education']);
            self::assertCount(1, $data['course']);
            self::assertCount(1, $data['workexp']);
            $people->update($account, $id, ['c_P_LastName' => 'معدل'], $data['version']);
            self::assertSame(' 0001 ', DB::table('op_person')->where('id', $id)->value('c_P_Mobile'));
            self::assertSame('web', DB::table('operational_entities')->where('id', $id)->value('identity_kind'));
            self::assertSame(0, DB::table('operational_origins')->count());
            self::assertSame(0, DB::table('source_records')->count());
            self::assertGreaterThan(0, DB::table('account_audit_events')->where('event_type', 'people.updated')->count());
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        }
        self::assertSame(0, DB::table('migrations')->count());
    }
}
