<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\FinanceService;
use App\Services\Finance\PaymentScheduleService;
use App\Services\Finance\PurchasingService;
use App\Services\Lifecycle\LifecycleService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** Opt-in finance writes against the actual approved schema on an empty disposable MySQL target. */
final class FinanceOperationalWorkflowTest extends TestCase
{
    public function test_finance_writes_use_the_approved_mysql_schema_columns_and_constraints(): void
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
            FinanceFixture::grant($account, [1]);
            $intake = ApplicationFixture::intake(1);
            $catalog = FinanceFixture::catalog(1);
            $id = FinanceFixture::acceptedProject($account, $intake);
            $finance = app(FinanceService::class);
            $lifecycle = app(LifecycleService::class);
            FinanceFixture::withDonor($account, $id, $catalog['donors']['a']);

            $finance->saveLoan($account, $id, FinanceFixture::loanInput(['amount' => '2500.75', 'returned' => '3000', 'payment' => '250.5', 'group_id' => $catalog['groups']['b']]), $finance->show($account, $id)['version']);
            $loan = DB::table('op_loan')->first();
            self::assertSame('USD', $loan->c_Currency);
            self::assertSame('2500.75', $loan->c_Loan_Amount);
            self::assertEqualsWithDelta(3000.0, (float) $loan->c_Loan_ReturnedAmount__76a7e234, 0.0);
            self::assertEqualsWithDelta(250.5, (float) $loan->c_Loan_PaymentsAmount__76a7e234, 0.0);
            self::assertNull($loan->c_Loan_ReturnedAmount__2ce06a9e);
            self::assertEqualsWithDelta(12.0, (float) $loan->c_Loan_PaymentsCount, 0.0);
            self::assertEqualsWithDelta(120.0, (float) $loan->c_Loan_Rate, 0.0);
            self::assertSame(' exact  note ', $loan->c_Loan_Note);
            self::assertSame('Financed', $lifecycle->panel($account, $id)['current']->state_name_en);
            self::assertSame($catalog['task'], (int) DB::table('op_task_microproject')->where('c_State', 1)->value('ref_bbc62ceac64e_id'));

            $loanId = (int) $loan->id;
            $rows = [
                ['amount' => '250', 'due_date' => '2026-11-01', 'paid' => '1', 'received_date' => '2026-11-03', 'note' => 'ملاحظة'],
                ['amount' => '-50', 'due_date' => '2026-12-01', 'paid' => '0'],
            ];
            $finance->savePayments($account, $id, $loanId, $rows, $finance->show($account, $id)['version']);
            $payments = DB::table('op_payment')->orderBy('id')->get();
            self::assertCount(2, $payments);
            self::assertSame('Paid', $payments[0]->c_Pay_IsPaid);
            self::assertEqualsWithDelta(-50.0, (float) $payments[1]->c_Pay_Amount, 0.0);
            self::assertSame('2026-12-01', $payments[1]->c_Pay_RecievedOnDate);
            self::assertSame('ملاحظة', $payments[0]->c_Pay_Note);
            $model = $finance->show($account, $id)['loans']->first();
            self::assertSame('250', $model['summary']['paid_amount']);
            self::assertSame('2750', $model['summary']['remaining']);
            try {
                $finance->savePayments($account, $id, $loanId, $rows, str_repeat('0', 64));
                self::fail('A stale version must be refused.');
            } catch (HttpException $exception) {
                self::assertSame(409, $exception->getStatusCode());
            }
            self::assertSame(2, DB::table('op_payment')->count());

            $schedule = app(PaymentScheduleService::class);
            $pending = $schedule->schedule($account, ['months' => [12], 'year' => 2026]);
            self::assertSame(1, $pending['rows']->total());
            self::assertSame('-50', $pending['totals']->first()->total);
            self::assertSame('USD', $pending['totals']->first()->currency);
            self::assertSame(1, $schedule->schedule($account, ['state' => 'paid', 'group' => 'group b'])['rows']->total());

            $files = app(ExecutiveFileService::class);
            $files->save($account, $id, null, ['number' => 'EX-1', 'begin_date' => '2026-01-31', 'impound_date' => '2026-02-01', 'impound_type' => 'نوع', 'months' => '1'], $files->show($account, $id)['version']);
            self::assertSame('2026-02-28', $files->show($account, $id)['files']->first()->expires);
            $purchasing = app(PurchasingService::class);
            $purchasing->save($account, $id, [['name' => 'مثقاب', 'quantity' => '2', 'price' => '10.5', 'currency' => 'SYP', 'category_id' => (string) $catalog['categories']['tools']]], $purchasing->show($account, $id)['version']);
            self::assertSame('10.50', DB::table('op_purchasing')->value('c_Price'));
            self::assertSame('web', DB::table('operational_entities')->where('id', $loanId)->value('identity_kind'));
            self::assertSame(0, DB::table('operational_origins')->count());
            self::assertGreaterThan(0, DB::table('account_audit_events')->where('event_type', 'finance.payments_saved')->count());
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        }
        self::assertSame(0, DB::table('migrations')->count());
    }
}
