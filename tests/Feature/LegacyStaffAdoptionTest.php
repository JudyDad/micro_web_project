<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Staff\LegacyStaffAdopter;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class LegacyStaffAdoptionTest extends TestCase
{
    private WebAccount $finance;

    private WebAccount $comm;

    private int $project;

    private int $task;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        ApplicationFixture::sqliteSchema();
        $admin = ApplicationFixture::account();
        $this->project = FinanceFixture::acceptedProject($admin, ApplicationFixture::intake(1));
        DB::table('web_notices')->delete();
        $this->task = StaffFixture::task(1, 16, 'Purchases');
        $money = StaffFixture::user(1, 41, 'finance.old', ['UserRoleID' => 3, 'IsCashier' => 1, 'Email' => ' Finance.Old@Example.org ']);
        $comm = StaffFixture::user(1, 42, 'comm.old', ['UserRoleID' => 2, 'IsCommunication' => 1, 'IsME' => 1, 'Email' => 'not-an-email']);
        $away = StaffFixture::user(1, 43, 'away.old', ['UserRoleID' => 6]);
        $unlinked = StaffFixture::user(1, 44, 'unlinked.old', ['UserRoleID' => 3]);
        $this->finance = StaffFixture::account('finance.account');
        $this->comm = StaffFixture::account('comm.account');
        $awayAccount = StaffFixture::account('away.account');
        StaffFixture::link($this->finance, 1, StaffFixture::principal(1, $money));
        StaffFixture::link($this->comm, 1, StaffFixture::principal(1, $comm));
        StaffFixture::link($awayAccount, 1, StaffFixture::principal(1, $away));
        DB::table('web_accounts')->whereIn('id', [$this->finance->id, $this->comm->id])->update(['email' => null]);
        StaffFixture::assign(1, $this->task, $comm, 42);
        StaffFixture::assign(1, $this->task, $unlinked, 44);
        $when = now()->toDateString();
        StaffFixture::legacyNotice(1, $money, 41, '1500', $when, ['c_Pay_ID' => 777, 'c_P_Name' => 'Beneficiary'], $this->project, $comm);
        StaffFixture::legacyNotice(1, $comm, 42, 'انتهاء المشتريات', $when, ['c_Pay_ID' => -3, 'c_Seen' => 1], $this->project);
        StaffFixture::legacyNotice(1, $comm, 42, 'Hello', $when, ['c_Pay_ID' => -100]);
        StaffFixture::legacyNotice(1, $unlinked, 44, 'for someone with no account', $when, ['c_Pay_ID' => -100]);
        foreach ([['Visit Opening-O', -21], ['براءة الذمة (Clearance)', -21]] as [$body, $pay]) {
            $id = StaffFixture::legacyNotice(1, $comm, 0, $body, $when, ['c_Pay_ID' => $pay, 'c_User_ID' => null], $this->project);
            DB::table('op_user_notification')->where('id', $id)->update(['ref_f8357f1eddef_id' => null, 'ref_f8357f1eddef_state' => 'null']);
        }
    }

    public function test_roles_duties_e_mail_tasks_and_notices_of_linked_users_are_adopted_once(): void
    {
        $adopter = app(LegacyStaffAdopter::class);
        $result = $adopter->adopt(1, 'adopt:test');
        self::assertSame(['accounts' => 3, 'roles' => 5, 'emails' => 1, 'tasks' => 1, 'notices' => 3, 'shared' => 2], $result);

        $roles = fn (WebAccount $account) => DB::table('account_city_roles')->where('account_id', $account->id)->whereNull('revoked_at')->orderBy('role')->pluck('role')->all();
        self::assertSame(['cashier', 'financial'], $roles($this->finance));
        self::assertSame(['communication', 'data-entry', 'monitoring'], $roles($this->comm), 'the role and the duties flagged on the user');
        self::assertSame([], DB::table('account_city_roles')->where('account_id', DB::table('web_accounts')->where('username', 'away.account')->value('id'))->pluck('role')->all(), 'Out of Service adopts nothing');
        self::assertGreaterThan(0, DB::table('account_city_capabilities')->where('account_id', $this->finance->id)->where('capability', 'finance.manage')->whereNull('revoked_at')->count(), 'the role grants its capabilities');

        self::assertSame('finance.old@example.org', DB::table('web_accounts')->where('id', $this->finance->id)->value('email'));
        self::assertNull(DB::table('web_accounts')->where('id', $this->comm->id)->value('email'), 'an invalid e-mail is not copied');
        self::assertSame([[$this->task, $this->comm->id]], DB::table('account_city_tasks')->get(['task_id', 'account_id'])->map(fn ($r) => [(int) $r->task_id, (int) $r->account_id])->all(), 'only the linked user\'s responsibility');

        $payment = DB::table('web_notices')->where('kind', 'payment')->first();
        self::assertSame($this->finance->id, (int) $payment->account_id);
        self::assertSame(777, (int) $payment->pay_key);
        self::assertSame('1500', $payment->body);
        self::assertSame($this->comm->id, (int) $payment->sender_account_id, 'the sender is the account linked to the sending user');
        self::assertSame($this->project, (int) $payment->project_id);
        $purchases = DB::table('web_notices')->where('kind', 'purchases')->first();
        self::assertSame([$this->comm->id, 1], [(int) $purchases->account_id, (int) $purchases->seen]);
        self::assertSame(['Hello'], DB::table('web_notices')->where('kind', 'message')->pluck('body')->all(), 'a notice for a user with no account is not copied');
        self::assertEqualsCanonicalizing(['visit', 'clearance'], DB::table('web_notices')->whereNull('account_id')->pluck('kind')->all());

        // Running it again changes nothing.
        $before = [DB::table('web_notices')->count(), DB::table('account_city_roles')->count(), DB::table('account_city_tasks')->count(), DB::table('account_audit_events')->count()];
        self::assertSame(['accounts' => 3, 'roles' => 0, 'emails' => 0, 'tasks' => 0, 'notices' => 0, 'shared' => 0], $adopter->adopt(1, 'adopt:test'));
        self::assertSame($before, [DB::table('web_notices')->count(), DB::table('account_city_roles')->count(), DB::table('account_city_tasks')->count(), DB::table('account_audit_events')->count()]);
    }

    public function test_a_dry_run_command_reports_and_changes_nothing_and_a_real_run_commits(): void
    {
        $this->artisan('staff:adopt-legacy', ['--dry-run' => true, '--city' => 1])->expectsOutputToContain('Dry run')->assertSuccessful();
        self::assertSame(0, DB::table('account_city_roles')->count());
        self::assertSame(0, DB::table('web_notices')->count());
        $this->artisan('staff:adopt-legacy', ['--city' => 1, '--decision' => 'adopt:cli'])->expectsOutputToContain('Done')->assertSuccessful();
        self::assertGreaterThan(0, DB::table('account_city_roles')->count());
        self::assertSame('adopt:cli', DB::table('account_city_roles')->value('decision_locator'));
        $this->artisan('staff:adopt-legacy', ['--city' => 99])->assertFailed();
        $this->artisan('staff:adopt-legacy', ['--decision' => 'bad decision!'])->assertFailed();
    }
}
