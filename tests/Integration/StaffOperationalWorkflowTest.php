<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountProvisioner;
use App\Services\AuthenticateAccount;
use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Staff\AccountAdminService;
use App\Services\Staff\NotificationInboxService;
use App\Services\Staff\TaskBoardService;
use App\Support\SyntheticFoundationFixture;
use Database\Schema\PreservationManifest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** Opt-in staff-operations writes and queries (P5.3) against the actual approved schema on an empty disposable MySQL target. */
final class StaffOperationalWorkflowTest extends TestCase
{
    /** @var array<string, int> source_records ids of the invented historical users of source 1 */
    private array $users = [];

    public function test_task_board_notices_and_account_administration_work_on_the_approved_mysql_schema(): void
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
                'owner' => $this->user(1, 101, 'task.owner'), 'comm' => $this->user(1, 102, 'comm.user', ['IsCommunication' => 1]), 'me' => $this->user(1, 103, 'me.team', ['IsME' => 1]),
                'fin' => $this->user(1, 104, 'finance.one', ['UserRoleID' => 3]), 'lawful' => $this->user(1, 105, 'lawyer', ['UserRoleID' => 7]),
                'dup1' => $this->user(1, 106, 'dup.user'), 'dup2' => $this->user(2, 201, 'dup.user'),
            ];
            $admin = ApplicationFixture::account();
            StaffFixture::grant($admin, [1, 2], ['accounts.manage', 'tasks.view', 'tasks.manage', 'tasks.manage_all', 'notifications.view', 'notifications.send', ...AccessRoles::capabilities('data-entry')]);
            FinanceFixture::grant($admin, [1, 2]);
            $intake = ApplicationFixture::intake(1);
            $catalog = FinanceFixture::catalog(1);
            $taskThree = (int) DB::table('op_task')->where('city_id', 1)->where('c_T_ID', 3)->value('id');
            $purchases = StaffFixture::task(1, 16, 'مشتريات');
            $project = FinanceFixture::acceptedProject($admin, $intake);
            foreach ([$taskThree, $purchases] as $task) {
                StaffFixture::assignTask($project, $task);
            }
            self::assertGreaterThan(0, DB::table('op_user_notification')->where('c_Body', 'like', 'تم تبديل حالة المشروع إلى%')->where('ref_5f0c797b1aae_id', $project)->count(), 'accepting is announced');

            // Task board: responsibility through a reviewed link, the filters and the purchases notice.
            $account = WebAccount::findOrFail(DB::table('web_accounts')->insertGetId(['username' => 'board.account', 'password_hash' => 'x', 'created_at' => now('UTC'), 'updated_at' => now('UTC')]));
            StaffFixture::grant($account, [1], ['tasks.view', 'tasks.manage', 'applications.view', 'notifications.view']);
            $principal = (int) app(AccountProvisioner::class)->ensurePrincipal(1, $this->users['owner'])->id;
            DB::table('account_legacy_links')->insert(['account_id' => $account->id, 'source_id' => 1, 'principal_id' => $principal, 'decision_locator' => 'synthetic:p53', 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
            StaffFixture::assign(1, $purchases, $this->users['owner'], 101);
            $board = app(TaskBoardService::class);
            $rows = $board->board($account, ['q' => 'Applicant', 'tasks' => [16 => '0'], 'state' => 1, 'type' => 'Synthetic type'])['rows'];
            self::assertSame([$project], $rows->pluck('id')->all());
            self::assertTrue($rows->first()->cells[16]['manageable']);
            self::assertFalse($rows->first()->cells[3]['manageable']);
            self::assertSame([], $board->board($account, ['q' => '100%'])['rows']->all());
            $board->toggle($account, $project, 16, 0, 1);
            $notice = DB::table('op_user_notification')->where('c_Pay_ID', -3)->first();
            self::assertSame('انتهاء المشتريات', $notice->c_Body);
            self::assertSame(102, (int) $notice->c_User_ID);
            self::assertSame(101, (int) $notice->c_Sender_ID, 'the linked staff identity is the sender');

            // Inbox: the communication user's notice is visible to an account linked to that user only.
            $inbox = app(NotificationInboxService::class);
            $other = WebAccount::findOrFail(DB::table('web_accounts')->insertGetId(['username' => 'comm.account', 'password_hash' => 'x', 'created_at' => now('UTC'), 'updated_at' => now('UTC')]));
            StaffFixture::grant($other, [1], ['notifications.view']);
            self::assertSame(0, $inbox->count($other));
            DB::table('account_legacy_links')->insert(['account_id' => $other->id, 'source_id' => 1, 'principal_id' => (int) app(AccountProvisioner::class)->ensurePrincipal(1, $this->users['comm'])->id, 'decision_locator' => 'synthetic:p53', 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
            self::assertSame(2, $inbox->count($other), 'the acceptance announcement and the purchases notice');
            self::assertSame(1, $inbox->markSeen($other, [(int) $notice->id]));
            self::assertSame(1, $inbox->count($other));
            $sent = $inbox->send($admin, ['city' => 1, 'to' => 'all', 'body' => 'مرحبا بالفريق', 'title' => 'اجتماع', 'date' => '2026-11-01']);
            self::assertGreaterThanOrEqual(7, $sent);
            self::assertSame($sent, DB::table('op_user_notification')->where('c_Pay_ID', -100)->count());

            // Finance-driven notices: first loan schedule, instalment notices, clearance and executive file.
            $finance = app(FinanceService::class);
            $funded = FinanceFixture::acceptedProject($admin, $intake);
            FinanceFixture::withDonor($admin, $funded, $catalog['donors']['a']);
            $version = fn () => $finance->show($admin, $funded)['version'];
            $loan = $finance->saveLoan($admin, $funded, FinanceFixture::loanInput(['date' => '2026-08-26', 'returned' => '240', 'payment' => '120']), $version());
            self::assertSame(['2026-08-31', '2027-02-28', '2027-08-28', '2028-02-28', '2028-08-28'], DB::table('op_user_notification')->where('ref_5f0c797b1aae_id', $funded)->where('c_Pay_ID', -21)->orderBy('id')->pluck('c_Date')->map(fn ($d) => substr((string) $d, 0, 10))->all());
            $finance->savePayments($admin, $funded, $loan, [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0'], ['amount' => '120', 'due_date' => '2026-12-01', 'paid' => '0']], $version());
            $payments = DB::table('op_payment')->orderBy('id')->pluck('id')->all();
            self::assertSame(2, DB::table('op_user_notification')->whereIn('c_Pay_ID', $payments)->count(), 'one notice per instalment for the single financial user');
            $finance->savePayments($admin, $funded, $loan, [['id' => (string) $payments[0], 'amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1'], ['id' => (string) $payments[1], 'amount' => '120', 'due_date' => '2026-12-01', 'paid' => '1', 'received_date' => '2026-12-30']], $version());
            self::assertSame('Finished', app(LifecycleService::class)->panel($admin, $funded)['current']->state_name_en);
            self::assertSame(1, DB::table('op_user_notification')->where('c_Body', 'براءة الذمة (Clearance)')->where('ref_5f0c797b1aae_id', $funded)->count());
            self::assertSame(1, DB::table('op_user_notification')->where('c_Pay_ID', -10)->where('ref_5f0c797b1aae_id', $funded)->count());
            $files = app(ExecutiveFileService::class);
            $files->save($admin, $funded, null, ['number' => 'EF-1', 'begin_date' => '2026-10-01', 'current_date' => '2026-10-01', 'impound_date' => '2026-10-02', 'impound_type' => 'x', 'months' => '6'], $files->show($admin, $funded)['version']);
            self::assertSame('2027-04-01', substr((string) DB::table('op_user_notification')->where('c_Pay_ID', -1199)->value('c_Date'), 0, 10));

            // Account administration on the real constraints.
            $service = app(AccountAdminService::class);
            self::assertContains('demo.user', $service->index($admin, ['q' => 'demo'])['rows']->pluck('username')->all());
            self::assertSame([], $service->index($admin, ['q' => '100%'])['rows']->all());
            $groups = $service->candidates($admin);
            self::assertCount(2, $groups->firstWhere('username', 'dup.user')->identities);
            $id = $service->create($admin, ['identities' => ['1:'.$this->users['dup1'], '2:'.$this->users['dup2']], 'credential' => '2:'.$this->users['dup2'], 'confirm' => '1', 'roles' => [1 => 'data-entry']]);
            $created = WebAccount::findOrFail($id);
            self::assertSame(2, DB::table('account_legacy_links')->where('account_id', $id)->count());
            self::assertSame(count(AccessRoles::capabilities('data-entry')), DB::table('account_city_capabilities')->where('account_id', $id)->where('city_id', 1)->whereNull('revoked_at')->count());
            self::assertSame($id, app(AuthenticateAccount::class)->attempt('dup.user', SyntheticFoundationFixture::PASSWORD)?->id);
            $service->saveCapabilities($admin, $id, 2, ['people.view', 'visits.view']);
            self::assertSame(['people.view', 'visits.view'], DB::table('account_city_capabilities')->where('account_id', $id)->where('city_id', 2)->whereNull('revoked_at')->orderBy('capability')->pluck('capability')->all());
            $service->setDisabled($admin, $id, true);
            self::assertNotNull(DB::table('web_accounts')->where('id', $id)->value('disabled_at'));
            self::assertNull(app(AuthenticateAccount::class)->attempt('dup.user', SyntheticFoundationFixture::PASSWORD), 'a disabled account cannot log in');
            self::assertSame($id, (int) DB::table('account_audit_events')->where('event_type', 'account.provisioned')->where('actor_account_id', $admin->id)->value('subject_account_id'));
            $directory = $service->staff($admin, []);
            self::assertSame(['dup.user', 'dup.user'], collect($directory['groups'])->flatMap(fn ($g) => $g['users'])->filter(fn ($u) => $u->account === 'dup.user')->pluck('username')->all());
            // The account tables reject a second account for a linked identity and a duplicate username.
            $this->mustReject(fn () => DB::table('web_accounts')->insert(['username' => 'dup.user', 'password_hash' => 'x', 'created_at' => now('UTC'), 'updated_at' => now('UTC')]));
            $this->mustReject(fn () => DB::table('account_legacy_links')->insert(['account_id' => $account->id, 'source_id' => 1, 'principal_id' => $principal, 'decision_locator' => 'x', 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u')]));
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

    private function mustReject(callable $call): void
    {
        try {
            $call();
        } catch (QueryException) {
            return;
        }
        self::fail('The database must reject this write.');
    }
}
