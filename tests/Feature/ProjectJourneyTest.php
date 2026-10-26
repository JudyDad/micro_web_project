<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\VisitService;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** P5.4: one project followed over HTTP from acceptance through finance and visits to repayment, with retries and stale forms. */
final class ProjectJourneyTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $finance;

    private array $field;

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
        FieldFixture::grant($this->account);
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->finance = FinanceFixture::catalog(1);
        $this->field = FieldFixture::visitCatalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FieldFixture::assignVisitTasks($this->project, $this->field);
        ApplicationFixture::row('task_microproject', 1, ['c_State' => 0, 'c_Date' => '2026-10-19 00:00:00'], ['MicroProject_ID' => $this->project, 'Task_ID' => $this->finance['task']]);
        $this->actingAs($this->account);
    }

    public function test_project_goes_from_accepted_to_financed_visited_and_finished_with_one_event_per_step(): void
    {
        $base = '/en/applications/'.$this->project;
        self::assertSame(['On Hold', 'Accepted'], $this->states());
        StaffFixture::holds(StaffFixture::account('synthetic.monitor'), 1, 'monitoring');
        $noticesAtStart = DB::table('web_notices')->count();

        // A loan needs a donor: refused, nothing written.
        $loan = FinanceFixture::loanInput(['version' => $this->financeVersion(), '_form' => 'loan-new']);
        $this->post($base.'/finance/loan', $loan)->assertSessionHasErrors('loan');
        self::assertSame(0, DB::table('op_loan')->count());
        self::assertSame(['On Hold', 'Accepted'], $this->states());

        $this->post($base.'/finance/donor', ['version' => $this->financeVersion(), 'donor_id' => $this->finance['donors']['a']])->assertRedirect();

        // Saving the loan finances the project, completes the loan task and schedules visits; a retry of the same form adds nothing.
        $loan = FinanceFixture::loanInput(['version' => $this->financeVersion(), '_form' => 'loan-new']);
        $this->post($base.'/finance/loan', $loan)->assertRedirect()->assertSessionHasNoErrors();
        $this->post($base.'/finance/loan', $loan)->assertStatus(409);
        self::assertSame(1, DB::table('op_loan')->count());
        self::assertSame(['On Hold', 'Accepted', 'Financed'], $this->states());
        self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->finance['task'])->value('c_State'));
        $scheduled = DB::table('web_notices')->count() - $noticesAtStart;
        self::assertGreaterThan(0, $scheduled, 'loan creation should schedule visit notices');
        $this->get($base)->assertOk()->assertSee('Financed');
        $this->get('/en/payments')->assertOk();

        // The opening visit is recorded once; editing it updates the same rows and a stale form is refused.
        $visit = $this->opening(['version' => $this->visitVersion()]);
        $this->post($base.'/visits/O-op', $visit)->assertRedirect()->assertSessionHasNoErrors();
        $this->post($base.'/visits/O-op', $visit)->assertStatus(409);
        self::assertSame(1, DB::table('op_visit')->count());
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->field['tasks'][25])->value('c_State'));
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->field['tasks'][31])->value('c_State'));
        self::assertSame(['On Hold', 'Accepted', 'Financed'], $this->states(), 'a visit must not change the lifecycle');
        $this->get($base.'/visits')->assertOk();

        // Repaying the loan in full finishes the project; a duplicate submission cannot add a second Finished state.
        $loanId = (int) DB::table('op_loan')->value('id');
        $rows = [['amount' => '1200', 'due_date' => '2026-11-01', 'paid' => '1', 'received_date' => '2026-11-01', 'note' => '']];
        $submit = ['version' => $this->financeVersion(), 'loan_id' => $loanId, 'rows' => $rows, '_form' => 'loan-'.$loanId];
        $this->post($base.'/finance/installments', $submit)->assertRedirect()->assertSessionHasNoErrors();
        $this->post($base.'/finance/installments', $submit)->assertStatus(409);
        self::assertSame(1, DB::table('op_payment')->count());
        self::assertSame(['On Hold', 'Accepted', 'Financed', 'Finished'], $this->states());
        self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
        self::assertSame('Finished', app(LifecycleService::class)->panel($this->account, $this->project)['current']->state_name_en);

        // The final picture agrees on every page that shows it.
        $this->get($base)->assertOk()->assertSee('Finished');
        $this->get('/en/statistics')->assertOk()->assertSee('Finished');
        $this->get('/en/applications?status='.$this->intake['states']['Finished'])->assertOk();
        self::assertSame(1, DB::table('op_loan')->count());
        self::assertSame(1, DB::table('op_visit')->count());
    }

    public function test_a_loan_without_a_monitoring_account_finances_the_project_but_schedules_no_visits_and_says_so(): void
    {
        $base = '/en/applications/'.$this->project;
        $this->post($base.'/finance/donor', ['version' => $this->financeVersion(), 'donor_id' => $this->finance['donors']['a']])->assertRedirect();
        $this->post($base.'/finance/loan', FinanceFixture::loanInput(['version' => $this->financeVersion(), '_form' => 'loan-new']))->assertRedirect();
        self::assertSame(['On Hold', 'Accepted', 'Financed'], $this->states());
        self::assertSame(0, DB::table('web_notices')->where('project_id', $this->project)->count());
        $this->get($base.'/finance')->assertOk()->assertSee(__('finance.visit_notices_missing'));
    }

    public function test_an_imported_project_pointing_at_a_desktop_user_still_opens(): void
    {
        $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'microproject' && $r['child_columns'] === ['MP_EditedByUser_ID']);
        DB::table('op_microproject')->where('id', $this->project)->update([$relation['resolved_column'] => 987654, 'c_MP_EditedByUser_ID' => 7]);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Accepted');
        $this->get('/en/applications/'.$this->project.'/finance')->assertOk();
    }

    public function test_a_project_that_is_not_accepted_cannot_be_financed(): void
    {
        $other = FinanceFixture::acceptedProject($this->account, $this->intake);
        $lifecycle = app(LifecycleService::class);
        $lifecycle->transition($this->account, $other, $this->intake['states']['Rejected'], null, null, $lifecycle->panel($this->account, $other)['version']);
        $version = app(FinanceService::class)->show($this->account, $other)['version'];
        $this->post('/en/applications/'.$other.'/finance/donor', ['version' => $version, 'donor_id' => $this->finance['donors']['a']])->assertSessionHasErrors();
        $this->post('/en/applications/'.$other.'/finance/loan', FinanceFixture::loanInput(['version' => $version, '_form' => 'loan-new']))->assertSessionHasErrors('loan');
        self::assertSame(0, DB::table('op_loan')->count());
        self::assertSame(['On Hold', 'Accepted', 'Rejected'], $this->states($other));
    }

    public function test_every_step_is_refused_without_its_grant_and_for_other_cities(): void
    {
        $base = '/en/applications/'.$this->project;
        DB::table('account_city_capabilities')->whereIn('capability', ['finance.manage', 'visits.manage'])->update(['revoked_at' => now()]);
        $this->post($base.'/finance/loan', FinanceFixture::loanInput(['version' => $this->financeVersion(), '_form' => 'loan-new']))->assertForbidden();
        $this->post($base.'/visits/O-op', $this->opening(['version' => str_repeat('0', 64)]))->assertForbidden();
        DB::table('account_city_capabilities')->update(['revoked_at' => null]);
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->get($base.'/finance')->assertForbidden();
        $this->get($base.'/visits')->assertForbidden();
        self::assertSame(0, DB::table('op_loan')->count());
        self::assertSame(0, DB::table('op_visit')->count());
        DB::table('account_city_capabilities')->update(['revoked_at' => null]);
        self::assertSame(['On Hold', 'Accepted'], $this->states());
    }

    /** @return array<string> state names, oldest first */
    private function states(?int $project = null): array
    {
        return app(LifecycleService::class)->panel($this->account, $project ?? $this->project)['history']->sortBy('id')->pluck('state_name_en')->values()->all();
    }

    private function financeVersion(): string
    {
        return app(FinanceService::class)->show($this->account, $this->project)['version'];
    }

    private function visitVersion(): string
    {
        return app(VisitService::class)->hub($this->account, $this->project)['version'];
    }

    private function opening(array $overrides = []): array
    {
        return $overrides + [
            'date' => '2026-10-20', 'indicators' => 'indicators', 'street_id' => $this->field['street'], 'place_note' => 'Near the market',
            'q1_value' => '1', 'q1_text' => 'On time', 'q2_value' => '0', 'q2_text' => 'Delays', 'q3_value' => '1', 'q4_value' => '3', 'q4_text' => '75 percent',
            'q5_value' => '1', 'q6_value' => '0', 'q7_value' => '', 'q7_text' => 'Not tried yet',
            'office1_value' => '2', 'office1_text' => 'Kind staff', 'office6_value' => '0', 'other_comments' => 'More chairs',
        ];
    }
}
