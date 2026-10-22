<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Finance\FinanceService;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class FinanceHttpTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $catalog;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        FinanceFixture::grant($this->account);
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FinanceFixture::catalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FinanceFixture::withDonor($this->account, $this->project, $this->catalog['donors']['a']);
    }

    private function version(): string
    {
        return app(FinanceService::class)->show($this->account, $this->project)['version'];
    }

    private function url(string $path = 'finance', string $locale = 'en'): string
    {
        return '/'.$locale.'/applications/'.$this->project.'/'.$path;
    }

    public function test_finance_pages_render_in_both_languages_with_the_forms_a_manager_needs(): void
    {
        $this->actingAs($this->account);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Loan and instalments')->assertSee('Executive file')->assertSee('Purchases (legacy screen)');
        $this->get($this->url())->assertOk()->assertSee('Save the loan')->assertSee('Change donor')->assertSee('Synthetic donor A');
        $this->get($this->url('finance', 'ar'))->assertOk()->assertSee('dir="rtl"', false)->assertSee('حفظ القرض');
        $this->get($this->url('executive-files'))->assertOk()->assertSee('New executive file');
        $this->get($this->url('purchasing'))->assertOk()->assertSee('Purchases');
        $this->get($this->url('purchasing', 'ar'))->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/en/payments')->assertOk()->assertSee('Instalments schedule');
        $this->get('/ar/payments')->assertOk()->assertSee('جدول الأقساط');
    }

    public function test_loan_and_instalment_forms_save_validate_and_refuse_stale_submissions(): void
    {
        $this->actingAs($this->account);
        $loan = FinanceFixture::loanInput(['version' => $this->version(), '_form' => 'loan-new']);
        $this->post($this->url('finance/loan'), ['amount' => '0'] + $loan)->assertSessionHasErrors('amount');
        self::assertSame(0, DB::table('op_loan')->count());
        $this->post($this->url('finance/loan'), $loan)->assertRedirect();
        self::assertSame(1, DB::table('op_loan')->count());
        $this->post($this->url('finance/loan'), $loan)->assertStatus(409);
        self::assertSame(1, DB::table('op_loan')->count());

        $loanId = DB::table('op_loan')->value('id');
        $page = $this->get($this->url())->assertOk()->assertSee('Financed')->assertSee('Instalments');
        $rows = [['amount' => '120', 'due_date' => '2026-11-01', 'paid' => '0', 'received_date' => '', 'note' => '']];
        $submit = ['version' => $this->version(), 'loan_id' => $loanId, 'rows' => $rows, '_form' => 'loan-'.$loanId];
        $this->post($this->url('finance/installments'), ['rows' => [['amount' => '1.5'] + $rows[0]]] + $submit)->assertSessionHasErrors('rows.0.amount');
        $this->post($this->url('finance/installments'), $submit)->assertRedirect();
        $this->post($this->url('finance/installments'), $submit)->assertStatus(409);
        self::assertSame(1, DB::table('op_payment')->count());
        $this->get($this->url())->assertOk()->assertSee('2026-11-01')->assertSee('Remaining to return');
        $this->get('/en/payments')->assertOk()->assertSee('2026-11-01')->assertSee('USD');
        $this->get('/en/payments?year=2019')->assertOk()->assertDontSee('2026-11-01');
        $this->get('/en/payments?year=2017')->assertSessionHasErrors('year');
    }

    public function test_donor_group_executive_and_purchasing_forms_post_and_redirect(): void
    {
        $this->actingAs($this->account);
        $this->post($this->url('finance/groups'), ['group_name' => 'Web group', 'group_donor_id' => $this->catalog['donors']['a']])->assertRedirect();
        self::assertSame(1, DB::table('op_donorgroup')->where('c_Name', 'Web group')->count());
        $this->post($this->url('finance/groups'), ['group_name' => 'Web group', 'group_donor_id' => $this->catalog['donors']['a']])->assertSessionHasErrors('group_name');
        $this->post($this->url('finance/donor'), ['version' => $this->version(), 'donor_id' => $this->catalog['donors']['b']])->assertRedirect();
        self::assertSame('Synthetic donor B', app(FinanceService::class)->show($this->account, $this->project)['donor']->c_Name);

        $executive = ['version' => str_repeat('0', 64), 'number' => 'EX-9', 'begin_date' => '2026-05-01', 'impound_date' => '2026-05-02', 'months' => '2'];
        $this->post($this->url('executive-files'), $executive)->assertStatus(409);
        $executive['version'] = hash('sha256', json_encode([], JSON_THROW_ON_ERROR));
        $this->post($this->url('executive-files'), $executive)->assertRedirect();
        $this->get($this->url('executive-files'))->assertOk()->assertSee('EX-9')->assertSee('2026-07-01');

        $purchase = ['version' => hash('sha256', json_encode([], JSON_THROW_ON_ERROR)), 'rows' => [['name' => 'Drill', 'quantity' => '1', 'price' => '9.5', 'currency' => 'USD']]];
        $this->post($this->url('purchasing'), $purchase)->assertRedirect();
        $this->get($this->url('purchasing'))->assertOk()->assertSee('Drill');
        $this->post($this->url('purchasing'), $purchase)->assertStatus(409);
        self::assertSame(1, DB::table('op_purchasing')->count());
    }

    public function test_missing_grants_hide_and_refuse_every_finance_route(): void
    {
        DB::table('account_city_capabilities')->whereIn('capability', ['finance.view', 'finance.manage', 'executive_files.view', 'executive_files.manage', 'purchasing.view', 'purchasing.manage'])->update(['revoked_at' => now()]);
        $this->actingAs($this->account);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('Loan and instalments');
        $this->get('/en/applications/'.$this->project)->assertDontSee('Instalments</span>', false);
        foreach (['finance', 'executive-files', 'purchasing'] as $path) {
            $this->get($this->url($path))->assertForbidden();
        }
        $this->get('/en/payments')->assertForbidden();
        $this->post($this->url('finance/loan'), FinanceFixture::loanInput(['version' => str_repeat('0', 64)]))->assertForbidden();
        $this->post($this->url('finance/donor'), ['version' => str_repeat('0', 64), 'donor_id' => $this->catalog['donors']['b']])->assertForbidden();
        $this->post($this->url('finance/installments'), ['version' => str_repeat('0', 64), 'loan_id' => 1])->assertForbidden();
        $this->get('/en/applications/0/finance')->assertNotFound();
        self::assertSame(0, DB::table('op_loan')->count());
    }

    public function test_view_only_accounts_see_the_figures_without_forms(): void
    {
        app(FinanceService::class)->saveLoan($this->account, $this->project, FinanceFixture::loanInput(), $this->version());
        DB::table('account_city_capabilities')->where('capability', 'finance.manage')->update(['revoked_at' => now()]);
        $this->actingAs($this->account);
        $this->get($this->url())->assertOk()->assertSee('Remaining to return')->assertDontSee('Save the loan', false)->assertDontSee('Save instalments', false);
    }
}
