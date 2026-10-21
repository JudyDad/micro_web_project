<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\FinanceService;
use App\Services\Finance\PaymentScheduleService;
use App\Services\Finance\PurchasingService;
use App\Services\Lifecycle\LifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class FinanceWorkflowTest extends TestCase
{
    private WebAccount $account;

    private FinanceService $finance;

    private LifecycleService $lifecycle;

    private array $intake;

    private array $catalog;

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
        $this->intake = [1 => ApplicationFixture::intake(1), 2 => ApplicationFixture::intake(2)];
        $this->catalog = [1 => FinanceFixture::catalog(1), 2 => FinanceFixture::catalog(2)];
    }

    public function test_donor_is_mandatory_for_funded_states_and_forbidden_before(): void
    {
        $id = $this->project(accept: false);
        $this->assertStatus(422, fn () => $this->finance->assignDonor($this->account, $id, $this->catalog[1]['donors']['a'], $this->version($id)));
        self::assertSame('state', $this->finance->show($this->account, $id)['ineligible']);
        $this->accept($id);
        self::assertSame('donor', $this->finance->show($this->account, $id)['ineligible']);
        $this->assertStatus(422, fn () => $this->finance->assignDonor($this->account, $id, $this->catalog[2]['donors']['a'], $this->version($id)), 'a donor of another city');
        $this->finance->assignDonor($this->account, $id, $this->catalog[1]['donors']['a'], $this->version($id));
        self::assertSame('Synthetic donor A', $this->finance->show($this->account, $id)['donor']->c_Name);
        $this->assertStatus(422, fn () => $this->finance->assignDonor($this->account, $id, null, $this->version($id)), 'a funded project keeps its donor');
        $this->assertStatus(409, fn () => $this->finance->assignDonor($this->account, $id, $this->catalog[1]['donors']['b'], str_repeat('0', 64)));
        self::assertNull($this->finance->show($this->account, $id)['ineligible']);
    }

    public function test_first_loan_is_exact_marks_financed_and_completes_the_task_once(): void
    {
        $id = $this->fundedProject();
        $version = $this->version($id);
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['group_id' => $this->catalog[1]['groups']['b']]), $version);

        $loan = DB::table('op_loan')->first();
        self::assertSame('USD', $loan->c_Currency);
        self::assertEquals('1000', $loan->c_Loan_Amount);
        self::assertEquals('15000', $loan->c_ExchangeRate);
        self::assertEquals('1200', $loan->c_Loan_ReturnedAmount__76a7e234);
        self::assertEquals('120', $loan->c_Loan_PaymentsAmount__76a7e234);
        self::assertNull($loan->c_Loan_ReturnedAmount__2ce06a9e);
        self::assertEquals('10.0', $loan->c_Loan_PaymentsCount);
        self::assertEquals('120.0', $loan->c_Loan_Rate);
        self::assertSame(' exact  note ', $loan->c_Loan_Note);
        self::assertSame('R-1', $loan->c_Loan_ReceiptID);
        self::assertNull($loan->c_Loan_ID, 'a web loan has no fabricated legacy key');
        self::assertSame('Financed', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
        $show = $this->finance->show($this->account, $id);
        self::assertSame('Group B', $show['group']->c_Name);
        self::assertSame('Synthetic donor B', $show['donor']->c_Name, 'the group sets its donor');
        self::assertSame([1], DB::table('op_task_microproject as t')->join('op_task as k', 'k.id', '=', 't.ref_bbc62ceac64e_id')->where('k.c_T_ID', 5)->pluck('t.c_State')->map(fn ($v) => (int) $v)->all());

        // A repeated submission with the old version cannot add a second loan or a second Financed row.
        $this->assertStatus(409, fn () => $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(), $version));
        $this->assertStatus(409, fn () => $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(), $this->version($id)), 'a second loan');
        self::assertSame(1, DB::table('op_loan')->count());
        self::assertSame(1, $this->historyCount($id, 'Financed'));
    }

    public function test_loan_values_are_validated_and_a_failed_save_writes_nothing(): void
    {
        $id = $this->fundedProject();
        foreach ([
            ['currency' => 'EUR'], ['amount' => '0'], ['amount' => '10.123'], ['amount' => 'abc'], ['amount' => '12345678901'], ['exchange_rate' => '1.234'],
            ['returned' => '-5'], ['payment' => '1.005'], ['date' => '20-10-2026'], ['returned' => '1234567.89'], ['receipt' => str_repeat('x', 51)],
        ] as $bad) {
            $this->assertStatus(422, fn () => $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput($bad), $this->version($id)), json_encode($bad));
        }
        self::assertSame(0, DB::table('op_loan')->count());
        self::assertSame('Accepted', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['amount' => '1,000.50', 'exchange_rate' => '', 'returned' => '0', 'payment' => '']), $this->version($id));
        $loan = DB::table('op_loan')->first();
        self::assertEquals('1000.5', $loan->c_Loan_Amount);
        self::assertEquals('0', $loan->c_ExchangeRate);
        self::assertEquals('0.0', $loan->c_Loan_PaymentsCount, 'nothing to repay yields 0');
    }

    public function test_double_precision_cities_use_their_own_amount_columns(): void
    {
        $city = FinanceFixture::addDoubleCity($this->account);
        $intake = ApplicationFixture::intake($city);
        $catalog = FinanceFixture::catalog($city);
        $id = FinanceFixture::acceptedProject($this->account, $intake, $city);
        FinanceFixture::withDonor($this->account, $id, $catalog['donors']['a']);
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['returned' => '1234567.89', 'payment' => '1000.25']), $this->version($id));
        $loan = DB::table('op_loan')->where('city_id', $city)->first();
        self::assertEquals('1234567.89', $loan->c_Loan_ReturnedAmount__2ce06a9e);
        self::assertEquals('1000.25', $loan->c_Loan_PaymentsAmount__2ce06a9e);
        self::assertNull($loan->c_Loan_ReturnedAmount__76a7e234);
        self::assertSame('1234567.89', $this->finance->show($this->account, $id)['loans']->first()['values']['returned']);
    }

    public function test_editing_a_loan_changes_only_what_changed_and_keeps_legacy_values(): void
    {
        $id = $this->fundedProject();
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(), $this->version($id));
        $loan = DB::table('op_loan')->first();
        // A preserved legacy value (more decimals than the form would accept) must survive an unrelated edit.
        DB::table('op_loan')->where('id', $loan->id)->update(['c_Loan_Rate' => '12.345', 'c_Loan_PaymentsCount' => '9.99']);
        $input = FinanceFixture::loanInput(['loan_id' => $loan->id, 'note' => 'changed note']);
        $this->finance->saveLoan($this->account, $id, $input, $this->version($id));
        $after = DB::table('op_loan')->first();
        self::assertSame('changed note', $after->c_Loan_Note);
        self::assertEquals('12.345', $after->c_Loan_Rate);
        self::assertEquals('9.99', $after->c_Loan_PaymentsCount);
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['loan_id' => $loan->id, 'note' => 'changed note', 'returned' => '1320']), $this->version($id));
        $recalculated = DB::table('op_loan')->first();
        self::assertEquals('11.0', $recalculated->c_Loan_PaymentsCount);
        self::assertEquals('132.0', $recalculated->c_Loan_Rate);
        self::assertSame(1, $this->historyCount($id, 'Financed'));
        $this->assertStatus(404, fn () => $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(['loan_id' => 999999]), $this->version($id)));
    }

    public function test_instalments_follow_the_desktop_rules_and_reject_inexact_amounts(): void
    {
        [$id, $loan] = $this->loanProject();
        $rows = [
            ['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0', 'received_date' => '2026-11-09', 'note' => ' n1 '],
            ['amount' => '1,000', 'due_date' => '2026-12-01', 'paid' => '1', 'received_date' => '', 'note' => ''],
            ['id' => '', 'amount' => '', 'due_date' => '', 'note' => ''],
        ];
        foreach ([['amount' => '120.5'], ['amount' => '16777217'], ['amount' => 'x'], ['amount' => ''], ['due_date' => '2026-02-30'], ['received_date' => 'soon']] as $bad) {
            $this->assertStatus(422, fn () => $this->finance->savePayments($this->account, $id, $loan, [$bad + $rows[0]], $this->version($id)), json_encode($bad));
        }
        self::assertSame(0, DB::table('op_payment')->count());

        $this->finance->savePayments($this->account, $id, $loan, $rows, $this->version($id));
        $payments = DB::table('op_payment')->orderBy('id')->get();
        self::assertCount(2, $payments);
        self::assertSame('Not Paid', $payments[0]->c_Pay_IsPaid);
        self::assertSame('2026-11-01', $payments[0]->c_Pay_RecievedOnDate, 'an unpaid row records its due date as the received date');
        self::assertSame(' n1 ', $payments[0]->c_Pay_Note);
        self::assertSame('Paid', $payments[1]->c_Pay_IsPaid);
        self::assertSame('2026-12-01', $payments[1]->c_Pay_RecievedOnDate, 'a paid row without a received date defaults to the due date');
        self::assertEquals('1000', $payments[1]->c_Pay_Amount);
        self::assertNull($payments[0]->c_Loan_ID);
        $model = $this->finance->show($this->account, $id)['loans']->first();
        self::assertSame('1000', $model['summary']['paid_amount']);
        self::assertSame('200', $model['summary']['remaining']);
        self::assertSame(1, $model['summary']['paid_count']);
    }

    public function test_unchanged_instalments_are_not_revalidated_or_rewritten_and_stale_submits_are_refused(): void
    {
        [$id, $loan] = $this->loanProject();
        $this->finance->savePayments($this->account, $id, $loan, [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0', 'received_date' => '', 'note' => '']], $this->version($id));
        $payment = DB::table('op_payment')->first();
        // Legacy oddity: received date differs from due date on an unpaid row, and a note with surrounding whitespace.
        DB::table('op_payment')->where('id', $payment->id)->update(['c_Pay_RecievedOnDate' => '2026-11-20', 'c_Pay_Note' => '  legacy  ']);
        $row = ['id' => (string) $payment->id, 'amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0', 'received_date' => '2026-11-20', 'note' => '  legacy  '];
        $stale = $this->version($id);
        $this->finance->savePayments($this->account, $id, $loan, [$row], $stale);
        $kept = DB::table('op_payment')->first();
        self::assertSame('2026-11-20', $kept->c_Pay_RecievedOnDate);
        self::assertSame('  legacy  ', $kept->c_Pay_Note);

        $this->finance->savePayments($this->account, $id, $loan, [['paid' => '1'] + $row], $this->version($id));
        self::assertSame('Paid', DB::table('op_payment')->first()->c_Pay_IsPaid);
        // The earlier version cannot be replayed to add a new row.
        DB::table('op_payment')->where('id', $payment->id)->update(['c_Pay_Note' => 'touched elsewhere']);
        $this->assertStatus(409, fn () => $this->finance->savePayments($this->account, $id, $loan, [['amount' => '5', 'due_date' => '2026-12-01']], $stale));
        self::assertSame(1, DB::table('op_payment')->count());
        $this->assertStatus(422, fn () => $this->finance->savePayments($this->account, $id, $loan, [['id' => '999999', 'amount' => '1', 'due_date' => '2026-12-01']], $this->version($id)));
    }

    public function test_the_project_finishes_once_when_the_last_instalment_is_paid_near_the_end(): void
    {
        [$id, $loan] = $this->loanProject(['returned' => '240', 'payment' => '120']);
        $this->finance->savePayments($this->account, $id, $loan, [
            ['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1'], ['amount' => '120', 'due_date' => '2026-12-01', 'paid' => '0'],
        ], $this->version($id));
        self::assertSame('Financed', $this->lifecycle->panel($this->account, $id)['current']->state_name_en, 'the last row is unpaid');
        $ids = DB::table('op_payment')->orderBy('id')->pluck('id')->all();
        $paid = fn (string $amount = '120') => [
            ['id' => (string) $ids[0], 'amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1'], ['id' => (string) $ids[1], 'amount' => $amount, 'due_date' => '2026-12-01', 'paid' => '1'],
        ];
        $this->finance->savePayments($this->account, $id, $loan, $paid(), $this->version($id));
        self::assertSame('Finished', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
        $this->finance->savePayments($this->account, $id, $loan, $paid('121'), $this->version($id));
        $this->finance->savePayments($this->account, $id, $loan, $paid('121'), $this->version($id));
        self::assertSame(1, $this->historyCount($id, 'Finished'));
    }

    public function test_a_paid_last_row_far_from_the_end_does_not_finish_the_project(): void
    {
        [$id, $loan] = $this->loanProject(['returned' => '1200', 'payment' => '120']);
        $this->finance->savePayments($this->account, $id, $loan, [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '1']], $this->version($id));
        $model = $this->finance->show($this->account, $id)['loans']->first();
        self::assertFalse($model['summary']['near_end']);
        self::assertSame('Financed', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
    }

    public function test_the_schedule_totals_each_currency_separately_and_respects_city_grants(): void
    {
        [$id1, $loan1] = $this->loanProject(['group_id' => (string) $this->catalog[1]['groups']['a']]);
        $this->finance->savePayments($this->account, $id1, $loan1, [
            ['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0'], ['amount' => '80', 'due_date' => '2026-12-05', 'paid' => '0'], ['amount' => '30', 'due_date' => '2026-12-06', 'paid' => '1', 'received_date' => '2026-12-07'],
        ], $this->version($id1));
        $id2 = FinanceFixture::acceptedProject($this->account, $this->intake[2], 2);
        FinanceFixture::withDonor($this->account, $id2, $this->catalog[2]['donors']['a']);
        $this->finance->saveLoan($this->account, $id2, FinanceFixture::loanInput(['currency' => 'SP', 'amount' => '5000000', 'returned' => '6000000', 'payment' => '500000']), $this->version($id2));
        $loan2 = DB::table('op_loan')->where('city_id', 2)->value('id');
        $this->finance->savePayments($this->account, $id2, (int) $loan2, [['amount' => '500000', 'due_date' => '2026-11-15', 'paid' => '0']], $this->version($id2));

        $schedule = app(PaymentScheduleService::class);
        $all = $schedule->schedule($this->account, []);
        self::assertSame(['SP' => ['500000', 1], 'USD' => ['200', 2]], $all['totals']->mapWithKeys(fn ($t) => [$t->currency => [$t->total, $t->payments]])->all());
        self::assertSame(['2026-11-01', '2026-11-15', '2026-12-05'], $all['rows']->getCollection()->map(fn ($r) => substr($r->c_Pay_DueDate, 0, 10))->all());
        self::assertSame(['2026-12-06'], $schedule->schedule($this->account, ['state' => 'paid'])['rows']->getCollection()->map(fn ($r) => substr($r->c_Pay_DueDate, 0, 10))->all());
        self::assertSame(2, $schedule->schedule($this->account, ['months' => [11]])['rows']->total());
        self::assertSame(1, $schedule->schedule($this->account, ['year' => 2026, 'months' => [12]])['rows']->total());
        self::assertSame(0, $schedule->schedule($this->account, ['year' => 2025])['rows']->total());
        self::assertSame(2, $schedule->schedule($this->account, ['group' => 'group a'])['rows']->total(), 'the group filter is case-insensitive and only matches grouped projects');
        self::assertSame(1, $schedule->schedule($this->account, ['cities' => [2]])['rows']->total());

        DB::table('account_city_capabilities')->where('city_id', 2)->where('capability', 'finance.view')->update(['revoked_at' => now()]);
        self::assertSame(2, $schedule->schedule($this->account, ['cities' => [2]])['rows']->total(), 'a city without the grant is dropped from the filter, not exposed');
        self::assertSame([1], $schedule->cities($this->account)->pluck('id')->all());
    }

    public function test_grants_gate_every_finance_read_and_write_per_city(): void
    {
        $id = $this->fundedProject();
        $version = $this->version($id);
        DB::table('account_city_capabilities')->where('capability', 'finance.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput(), $version));
        $this->assertStatus(403, fn () => $this->finance->assignDonor($this->account, $id, $this->catalog[1]['donors']['b'], $version));
        $this->assertStatus(403, fn () => $this->finance->createGroup($this->account, $id, 'New group', $this->catalog[1]['donors']['a']));
        $this->assertStatus(403, fn () => $this->finance->savePayments($this->account, $id, 1, [], $version));
        self::assertFalse($this->finance->show($this->account, $id)['canManage']);
        DB::table('account_city_capabilities')->where('capability', 'finance.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->finance->show($this->account, $id));
        self::assertSame(0, DB::table('op_loan')->count());
        self::assertSame(0, DB::table('account_audit_events')->where('event_type', 'finance.loan_saved')->count());
    }

    public function test_group_creation_is_idempotent_by_name_and_city_scoped(): void
    {
        $id = $this->fundedProject();
        $group = $this->finance->createGroup($this->account, $id, 'New group', $this->catalog[1]['donors']['a']);
        self::assertSame($this->catalog[1]['donors']['a'], (int) DB::table('op_donorgroup')->where('id', $group)->value('ref_2e054b2f9e5d_id'));
        $this->assertStatus(422, fn () => $this->finance->createGroup($this->account, $id, 'New group', $this->catalog[1]['donors']['a']));
        $this->assertStatus(422, fn () => $this->finance->createGroup($this->account, $id, '', $this->catalog[1]['donors']['a']));
        $this->assertStatus(422, fn () => $this->finance->createGroup($this->account, $id, str_repeat('g', 51), $this->catalog[1]['donors']['a']));
        $this->assertStatus(422, fn () => $this->finance->createGroup($this->account, $id, 'Other', $this->catalog[2]['donors']['a']));
        $this->finance->createGroup($this->account, $id, 'New group', $this->catalog[1]['donors']['b']);
        self::assertSame(2 + 1 + 1, DB::table('op_donorgroup')->where('city_id', 1)->count());
    }

    public function test_executive_file_follows_the_desktop_date_rules(): void
    {
        $id = $this->fundedProject();
        $files = app(ExecutiveFileService::class);
        $version = $files->show($this->account, $id)['version'];
        foreach ([['number' => ''], ['begin_date' => ''], ['impound_date' => 'x'], ['months' => '101'], ['months' => '-1'], ['impound_type' => str_repeat('t', 101)]] as $bad) {
            $this->assertStatus(422, fn () => $files->save($this->account, $id, null, $bad + $this->fileInput(), $version), json_encode($bad));
        }
        self::assertSame(0, DB::table('op_exefile')->count());
        $fileId = $files->save($this->account, $id, null, $this->fileInput(['current_date' => '', 'begin_date' => '2026-01-31', 'months' => '1']), $version);
        $row = DB::table('op_exefile')->first();
        self::assertSame('2026-01-31', $row->c_ExeF_CurrentDate, 'a new file starts with the current date equal to the begin date');
        self::assertSame('2026-02-28', $files->show($this->account, $id)['files']->first()->expires, 'month arithmetic clamps to the end of the month');
        self::assertSame($fileId, (int) $row->id);
        $this->assertStatus(409, fn () => $files->save($this->account, $id, null, $this->fileInput(), $files->show($this->account, $id)['version']), 'only one file per project');
        $this->assertStatus(409, fn () => $files->save($this->account, $id, $fileId, $this->fileInput(), $version), 'stale');
        $files->save($this->account, $id, $fileId, $this->fileInput(['begin_date' => '2026-01-31', 'current_date' => '2026-03-31', 'months' => '1', 'number' => '']), $files->show($this->account, $id)['version']);
        self::assertSame('', DB::table('op_exefile')->value('c_ExeF_No'), 'update mode does not re-check the number');
        self::assertSame('2026-04-30', $files->show($this->account, $id)['files']->first()->expires);
        self::assertFalse($files->show($this->account, $id)['canCreate']);
        DB::table('account_city_capabilities')->where('capability', 'executive_files.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $files->save($this->account, $id, $fileId, $this->fileInput(), $files->show($this->account, $id)['version']));
    }

    public function test_purchases_are_validated_per_row_and_unchanged_rows_are_left_alone(): void
    {
        $id = $this->fundedProject();
        $purchasing = app(PurchasingService::class);
        $category = $this->catalog[1]['categories']['tools'];
        $good = ['name' => 'Drill', 'brand' => 'B', 'category_id' => (string) $category, 'quantity' => '2', 'price' => '10.50', 'currency' => 'SYP', 'invoice_date' => '2026-10-01', 'invoice_number' => 'INV', 'trader' => 'T'];
        foreach ([['name' => ''], ['quantity' => '-1'], ['quantity' => '1.5'], ['price' => '1.234'], ['price' => ''], ['currency' => 'EUR'], ['invoice_date' => '2026-13-01'], ['category_id' => (string) $this->catalog[2]['categories']['tools']]] as $bad) {
            $this->assertStatus(422, fn () => $purchasing->save($this->account, $id, [$bad + $good], $purchasing->show($this->account, $id)['version']), json_encode($bad));
        }
        self::assertSame(0, DB::table('op_purchasing')->count());
        $purchasing->save($this->account, $id, [$good, ['id' => '', 'name' => '', 'price' => '', 'quantity' => '']], $purchasing->show($this->account, $id)['version']);
        self::assertSame(1, DB::table('op_purchasing')->count());
        $item = DB::table('op_purchasing')->first();
        self::assertEquals('10.5', $item->c_Price);
        self::assertSame(2, (int) $item->c_Quantity);
        self::assertSame($category, (int) $item->ref_386acf17e07d_id);
        DB::table('op_purchasing')->where('id', $item->id)->update(['c_Brand' => 'legacy brand']);
        $row = ['id' => (string) $item->id, 'name' => 'Drill', 'brand' => 'legacy brand', 'category_id' => (string) $category, 'quantity' => '2', 'price' => '10.5', 'currency' => 'SYP', 'invoice_date' => '2026-10-01', 'invoice_number' => 'INV', 'trader' => 'T'];
        $purchasing->save($this->account, $id, [$row], $purchasing->show($this->account, $id)['version']);
        self::assertSame('legacy brand', DB::table('op_purchasing')->value('c_Brand'));
        $purchasing->save($this->account, $id, [['quantity' => '5'] + $row], $purchasing->show($this->account, $id)['version']);
        self::assertSame(5, (int) DB::table('op_purchasing')->value('c_Quantity'));
        self::assertSame(1, DB::table('op_purchasing')->count());
        $this->assertStatus(409, fn () => $purchasing->save($this->account, $id, [$good], str_repeat('1', 64)));
    }

    private function fileInput(array $overrides = []): array
    {
        return $overrides + ['number' => 'EX-1', 'begin_date' => '2026-05-01', 'current_date' => '2026-05-01', 'impound_date' => '2026-05-02', 'impound_type' => 'type', 'months' => '3'];
    }

    private function version(int $project): string
    {
        return $this->finance->show($this->account, $project)['version'];
    }

    private function project(bool $accept = true): int
    {
        $id = app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'Synthetic finance project', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[1]['person'], $this->intake[1]['references']);
        if ($accept) {
            $this->accept($id);
        }

        return $id;
    }

    private function accept(int $id): void
    {
        $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Accepted'], null, null, $this->lifecycle->panel($this->account, $id)['version']);
    }

    private function fundedProject(): int
    {
        $id = $this->project();
        FinanceFixture::withDonor($this->account, $id, $this->catalog[1]['donors']['a']);

        return $id;
    }

    /** @return array{0: int, 1: int} project and loan ids */
    private function loanProject(array $overrides = []): array
    {
        $id = $this->fundedProject();
        $this->finance->saveLoan($this->account, $id, FinanceFixture::loanInput($overrides), $this->version($id));

        return [$id, (int) DB::table('op_loan')->where('city_id', 1)->value('id')];
    }

    private function historyCount(int $project, string $state): int
    {
        return $this->lifecycle->history(DB::table('op_microproject')->where('id', $project)->first())->where('state_name_en', $state)->count();
    }

    private function assertStatus(int $status, callable $callback, string $message = ''): void
    {
        try {
            $callback();
            self::fail('Expected status '.$status.' '.$message);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->getStatusCode(), $message);
        } catch (ValidationException $exception) {
            self::assertSame($status, $exception->status, $message);
        }
    }
}
