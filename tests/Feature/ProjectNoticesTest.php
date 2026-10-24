<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class ProjectNoticesTest extends TestCase
{
    private WebAccount $account;

    private FinanceService $finance;

    private LifecycleService $lifecycle;

    private array $intake;

    private array $catalog;

    /** @var array<string, WebAccount> invented city-1 staff by role */
    private array $staff = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        FinanceFixture::grant($this->account);
        $this->finance = app(FinanceService::class);
        $this->lifecycle = app(LifecycleService::class);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FinanceFixture::catalog(1);
        foreach (['monitoring', 'financial', 'cashier', 'data-entry', 'legal', 'communication', 'guest', 'media', 'administrator'] as $role) {
            $this->staff[$role] = StaffFixture::account($role.'.user', [1 => [$role]]);
        }
        StaffFixture::account('damascus.finance', [2 => ['financial']]);
    }

    private function version(int $project): string
    {
        return $this->finance->show($this->account, $project)['version'];
    }

    private function funded(): int
    {
        $id = FinanceFixture::acceptedProject($this->account, $this->intake);
        FinanceFixture::withDonor($this->account, $id, $this->catalog['donors']['a']);

        return $id;
    }

    /** @return array{0: int, 1: int} project and loan ids */
    private function loanProject(array $loan = []): array
    {
        $id = $this->funded();
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput($loan), $this->version($id));

        return [$id, (int) DB::table('op_loan')->orderByDesc('id')->value('id')];
    }

    /** @return array<string, string> body => date of the project's shared visit notices */
    private function visitNotices(int $project): array
    {
        return DB::table('web_notices')->where('project_id', $project)->where('kind', 'visit')->orderBy('id')->get()
            ->mapWithKeys(fn ($row) => [$row->body => substr((string) $row->notice_date, 0, 10)])->all();
    }

    private function notices(int $project, string $kind): Collection
    {
        return DB::table('web_notices')->where('project_id', $project)->where('kind', $kind)->orderBy('id')->get();
    }

    private function holders(Collection $rows): array
    {
        $ids = $rows->pluck('account_id')->map(fn ($id) => (int) $id)->all();
        $names = DB::table('web_accounts')->whereIn('id', $ids)->pluck('username', 'id');
        $result = array_map(fn ($id) => $names[$id], $ids);
        sort($result);

        return $result;
    }

    public function test_the_first_loan_generates_the_standard_visit_schedule_once(): void
    {
        [$id] = $this->loanProject(['date' => '2026-08-26']);
        self::assertSame([
            'Visit Opening-O' => '2026-08-31', 'Visit M1-O' => '2027-02-28', 'Visit M2-O' => '2027-08-28', 'Visit M3-O' => '2028-02-28', 'Visit MFinal-O' => '2028-08-28',
        ], $this->visitNotices($id), 'each visit is six months after the previous one, with .NET month-end clamping');
        $first = $this->notices($id, 'visit')->first();
        self::assertNull($first->account_id, 'one shared copy, no recipient');
        self::assertSame(0, (int) $first->seen);
        self::assertSame('Synthetic Applicant ابن/ة', $first->title);
        self::assertSame($this->account->id, (int) $first->sender_account_id, 'the acting account is the sender');
        self::assertSame((int) DB::table('op_microproject')->where('id', $id)->value('c_MP_ID'), (int) $first->project_number, 'the desktop project number is kept');

        // Editing the loan never regenerates them, and a project that has notices is left alone.
        $loan = (int) DB::table('op_loan')->value('id');
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['loan_id' => $loan, 'note' => 'edited']), $this->version($id));
        self::assertCount(5, $this->visitNotices($id));
    }

    public function test_the_schedule_follows_the_project_kind(): void
    {
        // A vehicle sub-category (code 1) uses the letter V.
        DB::table('op_subcategory')->update(['c_ID' => 1]);
        [$vehicle] = $this->loanProject(['date' => '2026-10-20']);
        self::assertSame(['Visit Opening-V', 'Visit M1-V', 'Visit M2-V', 'Visit M3-V', 'Visit MFinal-V'], array_keys($this->visitNotices($vehicle)));
        DB::table('op_subcategory')->update(['c_ID' => 11]);

        // Solar projects get an opening and two visits, marked "(Solar)".
        DB::table('op_microprojecttype')->update(['c_Name' => 'طاقة شمسية']);
        [$solar] = $this->loanProject(['date' => '2026-10-20']);
        self::assertSame(['Visit Opening-O (Solar)' => '2026-10-25', 'Visit M1-O (Solar)' => '2027-04-25', 'Visit M2-O (Solar)' => '2027-10-25'], $this->visitNotices($solar));

        // A grant-funded rehabilitation project gets a single closing visit.
        DB::table('op_microprojecttype')->update(['c_Name' => 'ترميم']);
        $fund = ApplicationFixture::row('fundtype', 1, ['c_ID' => 1, 'c_Name' => 'منحة']);
        $project = $this->funded();
        $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_microproject' && $r['child_columns'] === ['MP_FundType_ID']);
        DB::table('op_microproject')->where('id', $project)->update([$relation['resolved_column'] => $fund, $relation['state_column'] => 'resolved']);
        $this->finance->saveLoan($this->account, $project, FinanceFixture::loanInput(['date' => '2026-10-20']), $this->version($project));
        self::assertSame(['Visit Closing-R' => '2026-10-25'], $this->visitNotices($project));
    }

    public function test_agricultural_visits_fall_in_april_or_november(): void
    {
        DB::table('op_microprojecttype')->update(['c_Name' => 'مشروع زراعي']);
        $cases = [
            '2026-11-20' => ['2027-04-25', '2027-11-25', '2028-04-25', '2028-11-25', '2029-04-25'],  // reference January → April
            '2026-03-01' => ['2026-11-06', '2027-04-06', '2027-11-06', '2028-04-06', '2028-11-06'],  // reference May → November
            '2026-09-10' => ['2027-04-15', '2027-11-15', '2028-04-15', '2028-11-15', '2029-04-15'],  // reference November → next April
            '2027-01-26' => ['2027-04-30', '2027-11-30', '2028-04-30', '2028-11-30', '2029-04-30'],  // reference day 31 becomes 30
        ];
        foreach ($cases as $loanDate => $expected) {
            [$id] = $this->loanProject(['date' => $loanDate]);
            $notices = $this->visitNotices($id);
            self::assertSame(['Visit Opening-O', 'Visit M1-O', 'Visit M2-O', 'Visit M3-O', 'Visit M4-O', 'Visit M5-O'], array_keys($notices), $loanDate);
            self::assertSame($expected, array_slice(array_values($notices), 1), $loanDate);
        }
    }

    public function test_no_schedule_without_monitoring_staff_or_when_the_project_already_has_visit_notices(): void
    {
        DB::table('account_city_roles')->where('role', 'monitoring')->update(['revoked_at' => now()]);
        [$id] = $this->loanProject();
        self::assertSame([], $this->visitNotices($id), 'the desktop refused to generate without M&E staff');
        DB::table('account_city_roles')->where('role', 'monitoring')->update(['revoked_at' => null]);

        $other = $this->funded();
        StaffFixture::notice(1, null, 'visit', 'Visit Opening-O', '2026-01-01', [], $other);
        $this->finance->saveLoan($this->account, $other, FinanceFixture::loanInput(), $this->version($other));
        self::assertSame(['Visit Opening-O'], array_keys($this->visitNotices($other)), 'a project with visit notices is left as it is');

        $disabled = $this->funded();
        DB::table('web_accounts')->where('id', $this->staff['monitoring']->id)->update(['disabled_at' => now()]);
        $this->finance->saveLoan($this->account, $disabled, FinanceFixture::loanInput(), $this->version($disabled));
        self::assertSame([], $this->visitNotices($disabled), 'a disabled account does not count as monitoring staff');
    }

    public function test_instalment_notices_go_to_financial_and_cashier_accounts_and_follow_the_instalment(): void
    {
        [$id, $loan] = $this->loanProject(['returned' => '360', 'payment' => '120']);
        $this->finance->savePayments($this->account, $id, $loan, [
            ['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0'], ['amount' => '1,200', 'due_date' => '2026-12-01', 'paid' => '1'], ['amount' => '120', 'due_date' => '2027-01-01', 'paid' => '0'],
        ], $this->version($id));
        $payments = DB::table('op_payment')->orderBy('id')->get();
        $first = DB::table('web_notices')->where('kind', 'payment')->where('pay_key', $payments[0]->id)->get();
        self::assertSame(['cashier.user', 'financial.user'], $this->holders($first), 'financial and cashier accounts of this city only');
        self::assertSame(['120'], $first->pluck('body')->unique()->values()->all());
        self::assertSame('2026-11-01', substr((string) $first->first()->notice_date, 0, 10));
        self::assertSame('Synthetic Applicant ابن/ة', $first->first()->title);
        self::assertSame(0, DB::table('web_notices')->where('pay_key', $payments[1]->id)->count(), 'a paid instalment is not announced');
        self::assertSame(4, DB::table('web_notices')->where('kind', 'payment')->count());

        // Paying it clears its notices, changing it moves them, unpaying it brings them back.
        $row = fn (int $n, array $over = []) => $over + ['id' => (string) $payments[$n]->id, 'amount' => (string) (int) $payments[$n]->c_Pay_Amount, 'due_date' => substr((string) $payments[$n]->c_Pay_DueDate, 0, 10), 'paid' => '0', 'received_date' => substr((string) $payments[$n]->c_Pay_RecievedOnDate, 0, 10)];
        $this->finance->savePayments($this->account, $id, $loan, [$row(0, ['paid' => '1', 'received_date' => '2026-11-03'])], $this->version($id));
        $key = fn (int $n) => DB::table('web_notices')->where('kind', 'payment')->where('pay_key', $payments[$n]->id)->orderBy('id');
        self::assertSame([1, 1], $key(0)->pluck('seen')->map(fn ($v) => (int) $v)->all());
        $this->finance->savePayments($this->account, $id, $loan, [$row(0, ['paid' => '0', 'amount' => '130', 'due_date' => '2026-11-15'])], $this->version($id));
        $moved = $key(0)->get();
        self::assertSame([0, 0], $moved->pluck('seen')->map(fn ($v) => (int) $v)->all());
        self::assertSame(['130'], $moved->pluck('body')->unique()->values()->all());
        self::assertSame('2026-11-15', substr((string) $moved->first()->notice_date, 0, 10));

        // An unchanged instalment leaves notices alone, even ones an account has already seen.
        $key(2)->update(['seen' => 1]);
        $this->finance->savePayments($this->account, $id, $loan, [$row(2)], $this->version($id));
        self::assertSame([1, 1], $key(2)->pluck('seen')->map(fn ($v) => (int) $v)->all());
    }

    public function test_an_imported_instalment_is_found_by_its_desktop_payment_number(): void
    {
        [$id, $loan] = $this->loanProject();
        $legacy = ApplicationFixture::row('payment', 1, ['c_Pay_ID' => 8123, 'c_Pay_Amount' => 100, 'c_Pay_DueDate' => '2026-09-01', 'c_Pay_IsPaid' => 'Not Paid', 'c_Pay_RecievedOnDate' => '2026-09-01'], ['Loan_ID' => $loan]);
        StaffFixture::notice(1, $this->staff['financial']->id, 'payment', '100', '2026-09-01', ['pay_key' => 8123], $id);
        $this->finance->savePayments($this->account, $id, $loan, [['id' => (string) $legacy, 'amount' => '100', 'due_date' => '2026-09-01', 'paid' => '1', 'received_date' => '2026-09-02']], $this->version($id));
        self::assertSame([1], DB::table('web_notices')->where('pay_key', 8123)->pluck('seen')->map(fn ($v) => (int) $v)->all());
    }

    public function test_finishing_the_loan_sends_one_clearance_notice_and_trophy_notices(): void
    {
        [$id, $loan] = $this->loanProject(['returned' => '240', 'payment' => '120']);
        $rows = [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1', 'received_date' => '2026-11-02'], ['amount' => '120', 'due_date' => '2026-12-01', 'paid' => '1', 'received_date' => '2026-12-30']];
        $this->finance->savePayments($this->account, $id, $loan, $rows, $this->version($id));
        self::assertSame('Finished', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
        $clearance = $this->notices($id, 'clearance');
        self::assertCount(1, $clearance);
        self::assertSame('براءة الذمة (Clearance)', $clearance->first()->body);
        self::assertSame('2026-12-31', substr((string) $clearance->first()->notice_date, 0, 10), 'the day after the last payment');
        self::assertNull($clearance->first()->account_id);
        $trophies = $this->notices($id, 'trophy');
        self::assertSame(['Visit (Trophy)O'], $trophies->pluck('body')->unique()->values()->all());
        self::assertSame(['communication.user'], $this->holders($trophies), 'one for each communication account');

        $ids = DB::table('op_payment')->orderBy('id')->pluck('id')->all();
        $again = [['id' => (string) $ids[0]] + $rows[0], ['id' => (string) $ids[1], 'amount' => '121'] + $rows[1]];
        $this->finance->savePayments($this->account, $id, $loan, $again, $this->version($id));
        self::assertCount(1, $this->notices($id, 'clearance'), 'saving again adds no second clearance notice');
        self::assertCount(1, $this->notices($id, 'trophy'));
    }

    public function test_an_executive_file_notifies_legal_accounts_and_the_date_follows_edits(): void
    {
        $id = FinanceFixture::acceptedProject($this->account, $this->intake);
        $files = app(ExecutiveFileService::class);
        $input = ['number' => 'EF-1', 'begin_date' => '2026-10-01', 'current_date' => '2026-10-01', 'impound_date' => '2026-10-02', 'impound_type' => 'type', 'months' => '6'];
        $version = fn () => $files->show($this->account, $id)['version'];
        $file = $files->save($this->account, $id, null, $input, $version());
        $notices = $this->notices($id, 'executive_file');
        self::assertSame(['legal.user'], $this->holders($notices), 'legal accounts only');
        self::assertSame('الملف التنفيذي', $notices->first()->body);
        self::assertSame('2027-04-01', substr((string) $notices->first()->notice_date, 0, 10), 'the expiry date');
        self::assertSame($this->account->id, (int) $notices->first()->sender_account_id);

        $files->save($this->account, $id, $file, ['current_date' => '2026-11-30', 'months' => '3'] + $input, $version());
        $notices = $this->notices($id, 'executive_file');
        self::assertCount(1, $notices);
        self::assertSame('2027-02-28', substr((string) $notices->first()->notice_date, 0, 10), 'the notice follows the new expiry date');
        $files->save($this->account, $id, $file, ['number' => 'EF-2'] + $input, $version());
        self::assertCount(1, $this->notices($id, 'executive_file'));
    }

    public function test_a_state_change_notifies_staff_roles_and_closing_states_clear_the_projects_notices(): void
    {
        $id = FinanceFixture::acceptedProject($this->account, $this->intake);
        $staffRoles = ['administrator.user', 'cashier.user', 'communication.user', 'data-entry.user', 'financial.user', 'legal.user', 'media.user', 'monitoring.user'];
        $announced = fn (string $state) => DB::table('web_notices')->where('project_id', $id)->where('kind', 'state')->where('body', 'تم تبديل حالة المشروع إلى: '.$state)->get();
        self::assertSame($staffRoles, $this->holders($announced('مقبول')), 'accepting is announced to every role except Guest, in this city only');
        StaffFixture::notice(1, $this->staff['data-entry']->id, 'message', 'old news', now()->toDateString(), [], $id);
        $total = DB::table('web_notices')->count();

        $this->transition($id, 'Rejected');
        self::assertSame($total, DB::table('web_notices')->count(), 'a rejection is not announced');
        self::assertSame(0, (int) DB::table('web_notices')->where('body', 'old news')->value('seen'));

        $this->transition($id, 'Closed');
        self::assertSame(1, (int) DB::table('web_notices')->where('body', 'old news')->value('seen'), 'closing clears what was there');
        self::assertSame([1], $announced('مقبول')->pluck('seen')->map(fn ($v) => (int) $v)->unique()->values()->all());
        $broadcast = $announced('مغلق');
        self::assertSame($staffRoles, $this->holders($broadcast));
        self::assertSame([0], $broadcast->pluck('seen')->map(fn ($v) => (int) $v)->unique()->values()->all(), 'the new notices stay unseen');
        self::assertSame($this->account->id, (int) $broadcast->first()->sender_account_id);

        $other = FinanceFixture::acceptedProject($this->account, $this->intake);
        $before = DB::table('web_notices')->count();
        $this->transition($other, 'Waiting');
        self::assertSame($before, DB::table('web_notices')->count(), 'waiting is not announced');
    }

    private function transition(int $project, string $state): void
    {
        $this->lifecycle->transition($this->account, $project, $this->intake['states'][$state], null, null, $this->lifecycle->panel($this->account, $project)['version']);
    }
}
