<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\AccountPasswords;
use App\Services\AccountProvisioner;
use App\Services\AuthenticateAccount;
use App\Services\Field\VisitScheduleService;
use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Staff\AccountAdminService;
use App\Services\Staff\LegacyStaffAdopter;
use App\Services\Staff\NotificationInboxService;
use App\Services\Staff\TaskBoardService;
use App\Support\SyntheticFoundationFixture;
use Database\Schema\PreservationManifest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** Opt-in staff-operations writes and queries (P5.3) against the actual approved schema on an empty disposable MySQL target. */
final class StaffOperationalWorkflowTest extends TestCase
{
    /** @var array<string, int> source_records ids of the invented historical users */
    private array $users = [];

    public function test_roles_tasks_notices_passwords_and_adoption_work_on_the_approved_mysql_schema(): void
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
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        DB::beginTransaction();
        try {
            app(SyntheticFoundationFixture::class)->create();
            $this->users = [
                'finance' => $this->user(1, 101, 'finance.old', ['UserRoleID' => 3, 'IsCashier' => 1, 'Email' => ' Finance.Old@Example.org ']),
                'comm' => $this->user(1, 102, 'comm.old', ['IsCommunication' => 1, 'IsME' => 1]),
                'dup1' => $this->user(1, 106, 'dup.user', ['Email' => 'dup@example.org']), 'dup2' => $this->user(2, 201, 'dup.user'),
            ];
            $admin = ApplicationFixture::account();
            StaffFixture::grant($admin, [1, 2], AccountAccess::CAPABILITIES);
            $intake = ApplicationFixture::intake(1);
            $catalog = FinanceFixture::catalog(1);
            $service = app(AccountAdminService::class);

            // Roles and accounts: plain accounts get a one-time password, roles are recorded and grant their capabilities.
            $made = [];
            foreach (['monitoring' => 'me.team', 'financial' => 'finance.new', 'legal' => 'lawyer', 'communication' => 'comm.new', 'data-entry' => 'data.entry'] as $role => $name) {
                $made[$role] = $service->createPlain($admin, ['username' => $name, 'email' => $name.'@example.org', 'roles' => [1 => $role]]);
            }
            self::assertSame(count(AccessRoles::capabilities('financial')), DB::table('account_city_capabilities')->where('account_id', $made['financial']['id'])->where('city_id', 1)->whereNull('revoked_at')->count());
            self::assertTrue(app(AccountPasswords::class)->mustChange(WebAccount::findOrFail($made['financial']['id'])));
            self::assertSame($made['financial']['id'], app(AuthenticateAccount::class)->attempt('finance.new', $made['financial']['password'])?->id);
            $this->mustReject(fn () => $service->createPlain($admin, ['username' => 'other', 'email' => 'FINANCE.NEW@example.org', 'roles' => [1 => 'guest']]), 'a duplicate e-mail is a validation error');

            // Task board: responsibility from the new table, the purchases notice to the communication role.
            $taskThree = (int) DB::table('op_task')->where('city_id', 1)->where('c_T_ID', 3)->value('id');
            $purchases = StaffFixture::task(1, 16, 'مشتريات');
            $project = FinanceFixture::acceptedProject($admin, $intake);
            foreach ([$taskThree, $purchases] as $task) {
                StaffFixture::assignTask($project, $task);
            }
            self::assertSame(['added' => 1, 'withdrawn' => 0], $service->saveTasks($admin, 1, [$purchases => [$made['communication']['id']]]));
            $communicationAccount = WebAccount::findOrFail($made['communication']['id']);
            $board = app(TaskBoardService::class);
            $rows = $board->board($communicationAccount, ['q' => 'Applicant', 'tasks' => [16 => '0'], 'state' => 1, 'type' => 'Synthetic type'])['rows'];
            self::assertSame([$project], $rows->pluck('id')->all());
            self::assertTrue($rows->first()->cells[16]['manageable']);
            self::assertFalse($rows->first()->cells[3]['manageable']);
            self::assertSame([], $board->board($communicationAccount, ['q' => '100%'])['rows']->all());
            $board->toggle($communicationAccount, $project, 16, 0, 1);
            $purchaseNotices = DB::table('web_notices')->where('kind', 'purchases')->get();
            self::assertSame([$made['communication']['id']], $purchaseNotices->pluck('account_id')->map(fn ($id) => (int) $id)->all());
            self::assertSame($communicationAccount->id, (int) $purchaseNotices->first()->sender_account_id);

            // Inbox, marking seen, messages and the schedule actions.
            $inbox = app(NotificationInboxService::class);
            self::assertGreaterThan(0, $inbox->count($communicationAccount), 'the purchases notice and the acceptance announcement');
            self::assertSame(1, $inbox->markSeen($communicationAccount, [(int) $purchaseNotices->first()->id]));
            $sent = $inbox->send($admin, ['city' => 1, 'to' => 'all', 'body' => 'مرحبا بالفريق', 'title' => 'اجتماع', 'date' => '2026-11-01']);
            self::assertGreaterThanOrEqual(5, $sent);
            self::assertSame($sent, DB::table('web_notices')->where('kind', 'message')->count());

            // Finance-driven notices: first-loan schedule (needs a monitoring account), instalments, clearance, executive file, state changes.
            $finance = app(FinanceService::class);
            $funded = FinanceFixture::acceptedProject($admin, $intake);
            FinanceFixture::withDonor($admin, $funded, $catalog['donors']['a']);
            $version = fn () => $finance->show($admin, $funded)['version'];
            $loan = $finance->saveLoan($admin, $funded, FinanceFixture::loanInput(['date' => '2026-08-26', 'returned' => '240', 'payment' => '120']), $version());
            $visits = DB::table('web_notices')->where('project_id', $funded)->where('kind', 'visit')->orderBy('id')->get();
            self::assertSame(['2026-08-31', '2027-02-28', '2027-08-28', '2028-02-28', '2028-08-28'], $visits->pluck('notice_date')->map(fn ($d) => substr((string) $d, 0, 10))->all());
            self::assertNull($visits->first()->account_id);
            $schedule = app(VisitScheduleService::class);
            $second = (int) $visits[1]->id;
            self::assertSame(4, $schedule->reschedule($admin, $second, '2027-03-14', 'Beneficiary travelling', true));
            self::assertSame(['2027-03-14', '2027-09-11', '2028-03-13', '2028-09-11'], DB::table('web_notices')->whereIn('id', [(int) $visits[1]->id, (int) $visits[2]->id, (int) $visits[3]->id, (int) $visits[4]->id])->orderBy('id')->pluck('notice_date')->map(fn ($d) => substr((string) $d, 0, 10))->all());
            self::assertSame('2027-02-28', substr((string) DB::table('web_notices')->where('id', $second)->value('original_date'), 0, 10));
            self::assertSame(5, $schedule->schedule($admin, ['months' => [8, 3, 9]])['rows']->total(), 'the MONTH() filter on MySQL');
            $finance->savePayments($admin, $funded, $loan, [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0'], ['amount' => '120', 'due_date' => '2026-12-01', 'paid' => '0']], $version());
            $payments = DB::table('op_payment')->orderBy('id')->pluck('id')->all();
            self::assertSame(2, DB::table('web_notices')->whereIn('pay_key', $payments)->count(), 'one notice per instalment for the single financial account');
            $finance->savePayments($admin, $funded, $loan, [['id' => (string) $payments[0], 'amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1'], ['id' => (string) $payments[1], 'amount' => '120', 'due_date' => '2026-12-01', 'paid' => '1', 'received_date' => '2026-12-30']], $version());
            self::assertSame('Finished', app(LifecycleService::class)->panel($admin, $funded)['current']->state_name_en);
            self::assertSame(1, DB::table('web_notices')->where('kind', 'clearance')->where('project_id', $funded)->count());
            self::assertSame(1, DB::table('web_notices')->where('kind', 'trophy')->where('project_id', $funded)->count());
            $files = app(ExecutiveFileService::class);
            $files->save($admin, $funded, null, ['number' => 'EF-1', 'begin_date' => '2026-10-01', 'current_date' => '2026-10-01', 'impound_date' => '2026-10-02', 'impound_type' => 'x', 'months' => '6'], $files->show($admin, $funded)['version']);
            self::assertSame('2027-04-01', substr((string) DB::table('web_notices')->where('kind', 'executive_file')->value('notice_date'), 0, 10));

            // Password reset on the real BINARY(32) token column.
            $passwords = app(AccountPasswords::class);
            self::assertNull($passwords->requestReset('me.team', 'wrong@example.org'));
            $token = $passwords->requestReset('me.team', 'ME.TEAM@example.org');
            self::assertNotNull($token);
            self::assertTrue($passwords->validToken($token));
            self::assertTrue($passwords->reset($token, 'A-new-password-2026'));
            self::assertFalse($passwords->validToken($token));
            self::assertSame($made['monitoring']['id'], app(AuthenticateAccount::class)->attempt('me.team', 'A-new-password-2026')?->id);
            self::assertFalse($passwords->mustChange(WebAccount::findOrFail($made['monitoring']['id'])));

            // Account creation from duplicate desktop identities (e-mail normalised from the legacy record), then adoption of legacy staff data.
            $id = $service->create($admin, ['identities' => ['1:'.$this->users['dup1'], '2:'.$this->users['dup2']], 'credential' => '1:'.$this->users['dup1'], 'confirm' => '1', 'roles' => [1 => 'data-entry']]);
            self::assertSame('dup@example.org', DB::table('web_accounts')->where('id', $id)->value('email'));
            self::assertSame($id, app(AuthenticateAccount::class)->attempt('dup.user', SyntheticFoundationFixture::PASSWORD)?->id);
            $oldFinance = WebAccount::findOrFail(DB::table('web_accounts')->insertGetId(['username' => 'finance.old', 'password_hash' => 'x', 'password_changed_at' => now('UTC'), 'created_at' => now('UTC'), 'updated_at' => now('UTC')]));
            DB::table('account_legacy_links')->insert(['account_id' => $oldFinance->id, 'source_id' => 1, 'principal_id' => (int) app(AccountProvisioner::class)->ensurePrincipal(1, $this->users['finance'])->id, 'decision_locator' => 'synthetic:p53', 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
            StaffFixture::assign(1, $purchases, $this->users['finance'], 101);
            StaffFixture::legacyNotice(1, $this->users['finance'], 101, '1500', '2026-11-02', ['c_Pay_ID' => 777, 'c_P_Name' => 'Beneficiary'], $project);
            $result = app(LegacyStaffAdopter::class)->adopt(1, 'adopt:test');
            self::assertSame(['cashier', 'financial'], DB::table('account_city_roles')->where('account_id', $oldFinance->id)->orderBy('role')->pluck('role')->all());
            self::assertSame('finance.old@example.org', DB::table('web_accounts')->where('id', $oldFinance->id)->value('email'));
            self::assertSame(1, DB::table('account_city_tasks')->where('account_id', $oldFinance->id)->count());
            self::assertSame(1, DB::table('web_notices')->where('kind', 'payment')->where('pay_key', 777)->count());
            self::assertGreaterThanOrEqual(1, $result['notices']);
            self::assertSame(0, app(LegacyStaffAdopter::class)->adopt(1, 'adopt:test')['notices'], 'a second run copies nothing');

            // The database itself rejects what the services forbid.
            $row = ['city_id' => 1, 'source_id' => 1, 'kind' => 'message', 'notice_date' => '2026-11-01', 'body' => 'x', 'created_at' => now('UTC')->format('Y-m-d H:i:s.u')];
            $this->mustReject(fn () => DB::table('web_notices')->insert($row + ['account_id' => null]));
            $this->mustReject(fn () => DB::table('web_notices')->insert(['kind' => 'nonsense', 'account_id' => $admin->id] + $row));
            $this->mustReject(fn () => DB::table('web_notices')->insert(['seen' => 2, 'account_id' => $admin->id] + $row));
            $this->mustReject(fn () => DB::table('web_notices')->insert(['project_id' => 999999, 'account_id' => $admin->id] + $row));
            $this->mustReject(fn () => DB::table('account_city_tasks')->insert(['account_id' => $oldFinance->id, 'city_id' => 1, 'source_id' => 1, 'task_id' => $purchases, 'assigned_at' => now('UTC')->format('Y-m-d H:i:s.u')]));
            $this->mustReject(fn () => DB::table('account_city_roles')->insert(['account_id' => $oldFinance->id, 'city_id' => 1, 'role' => 'financial', 'decision_locator' => 'x', 'assigned_at' => now('UTC')->format('Y-m-d H:i:s.u')]));
            $this->mustReject(fn () => DB::table('web_accounts')->where('id', $id)->update(['email' => 'finance.new@example.org']));
            $this->mustReject(fn () => DB::table('account_password_resets')->insert(['account_id' => $admin->id, 'token_sha256' => random_bytes(32), 'requested_at' => '2026-11-01 10:00:00', 'expires_at' => '2026-11-01 09:00:00']));
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        }
        self::assertSame(0, DB::table('migrations')->count());
    }

    /** A published historical user of a source, as SyntheticFoundationFixture creates them; returns its source_records id. */
    private function user(int $source, int $userId, string $name, array $flags = []): int
    {
        $snapshot = (int) DB::table('snapshots')->where('source_id', $source)->value('id');
        $run = (int) DB::table('migration_runs')->where('snapshot_id', $snapshot)->value('id');
        $tableId = PreservationManifest::tableIds(PreservationManifest::load())['user'];
        $key = 'synthetic:p53:'.$source.':user:'.$userId;
        $record = DB::table('source_records')->insertGetId([
            'source_id' => $source, 'table_id' => $tableId, 'snapshot_id' => $snapshot, 'source_key' => $key, 'key_sha256' => hash('sha256', $key, true),
            'key_kind' => 'primary', 'import_run_id' => $run, 'resolution_status' => 'synthetic',
        ]);
        $manifest = PreservationManifest::load();
        $city = [1 => 'Aleppo', 2 => 'Damascus'][$source];
        $definition = collect($manifest['tables'])->first(fn ($entry) => $entry['city'] === $city && $entry['source_table'] === 'user');
        $row = ['record_id' => $record, 'origin_source_id' => $source, 'origin_table_id' => $tableId];
        foreach ($definition['columns'] as $column) {
            if (str_contains($column['target_definition'], 'NOT NULL') && ! str_contains($column['target_definition'], 'DEFAULT')) {
                $row[$column['target_column']] = preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|float|double|decimal)/', $column['target_definition']) ? 0 : '';
            }
        }
        DB::table($definition['target_table'])->insert(array_replace($row, [
            'UserID' => $userId, 'UserName' => $name, 'Password' => StaffFixture::LEGACY_PASSWORD_HASH, 'Email' => '', 'UserRoleID' => 2, 'IsVisitor' => 0, 'IsME' => 0, 'IsCommunication' => 0, 'IsCashier' => 0,
        ], $flags));

        return $record;
    }

    /** The database or the services must refuse this write. */
    private function mustReject(callable $call, string $message = 'The database must reject this write.'): void
    {
        try {
            $call();
        } catch (QueryException|ValidationException) {
            return;
        }
        self::fail($message);
    }
}
