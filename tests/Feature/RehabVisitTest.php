<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\RehabVisitService;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class RehabVisitTest extends TestCase
{
    private WebAccount $account;

    private RehabVisitService $rehab;

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
        FieldFixture::grant($this->account);
        $this->rehab = app(RehabVisitService::class);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FieldFixture::visitCatalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FieldFixture::assignVisitTasks($this->project, $this->catalog);
    }

    private function makeRehab(): void
    {
        DB::table('op_microprojecttype')->where('id', $this->intake['references']['MP_Type_ID'])->update(['c_Name' => 'ترميم منزل']);
        $fund = ApplicationFixture::row('fundtype', 1, ['c_ID' => 3, 'c_Name' => 'منحة', 'c_Name_en' => 'Grant']);
        DB::table('op_microproject')->where('id', $this->project)->update(app(OperationalTables::class)->link('microproject', 'MP_FundType_ID', $fund));
    }

    private function version(): string
    {
        return $this->rehab->form($this->account, $this->project)['version'];
    }

    private function input(array $overrides = []): array
    {
        $input = ['date' => '2026-11-05'];
        foreach (RehabVisitService::QUESTIONS as $n => $type) {
            $input['q'.$n.'_value'] = $type === 'yes_no' ? '1' : '2';
            $input['q'.$n.'_text'] = '';
        }
        $input['q5_value'] = 'na';
        $input['q13_value'] = '0';
        $input['q15_text'] = ' exact  note ';

        return $overrides + $input;
    }

    private function rejected(string $field, callable $call): void
    {
        try {
            $call();
            self::fail('Expected a validation error for '.$field);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());
        }
    }

    public function test_the_closing_visit_stores_all_answers_finishes_the_project_once_and_saves_edits(): void
    {
        $this->makeRehab();
        $id = $this->rehab->save($this->account, $this->project, $this->input(), $this->version());
        $visit = DB::table('op_visit')->where('id', $id)->first();
        self::assertSame('R-cl', $visit->c_Kind);
        self::assertSame('synthetic.applications', $visit->c_Created_By);
        $row = DB::table('op_rehabvisit')->first();
        self::assertSame([2, 2, 2, 2, -1, 2, 2, 2, 2, 2, 2, 2, 0, 1, 1], array_map(fn ($n) => (int) $row->{'c_Ans'.$n.'_Value'}, range(1, 15)));
        self::assertSame(' exact  note ', $row->c_Ans15);
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][31])->value('c_State'));
        $state = app(OperationalTables::class)->relation('microproject_state', 'State_ID')['resolved_column'];
        self::assertSame(1, DB::table('op_microproject_state')->where($state, $this->intake['states']['Finished'])->count());

        $this->rehab->save($this->account, $this->project, $this->input(['q6_value' => '0', 'q2_text' => 'Slow']), $this->version());
        self::assertSame(1, DB::table('op_visit')->count());
        self::assertSame(1, DB::table('op_rehabvisit')->count());
        $row = DB::table('op_rehabvisit')->first();
        self::assertSame(0, (int) $row->c_Ans6_Value, 'edits are saved (the desktop edit path lost them)');
        self::assertSame('Slow', $row->c_Ans2);
        self::assertSame('synthetic.applications', DB::table('op_visit')->value('c_Edited_By'));
        self::assertSame(1, DB::table('op_microproject_state')->where($state, $this->intake['states']['Finished'])->count());
    }

    public function test_every_question_must_be_answered_and_choices_are_checked(): void
    {
        $this->makeRehab();
        $this->rejected('q3_value', fn () => $this->rehab->save($this->account, $this->project, $this->input(['q3_value' => '']), $this->version()));
        $this->rejected('q3_value', fn () => $this->rehab->save($this->account, $this->project, $this->input(['q3_value' => 'na']), $this->version()));
        $this->rejected('q7_value', fn () => $this->rehab->save($this->account, $this->project, $this->input(['q7_value' => '3']), $this->version()));
        $this->rejected('q14_value', fn () => $this->rehab->save($this->account, $this->project, $this->input(['q14_value' => '2']), $this->version()));
        $this->rejected('date', fn () => $this->rehab->save($this->account, $this->project, $this->input(['date' => 'x']), $this->version()));
        self::assertSame(0, DB::table('op_visit')->count());
    }

    public function test_only_rehabilitation_grant_projects_can_have_it_and_stale_saves_are_refused(): void
    {
        try {
            $this->rehab->form($this->account, $this->project);
            self::fail('A non-rehabilitation project has no rehabilitation visit.');
        } catch (HttpException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
        $this->makeRehab();
        $version = $this->version();
        $this->rehab->save($this->account, $this->project, $this->input(), $version);
        try {
            $this->rehab->save($this->account, $this->project, $this->input(), $version);
            self::fail('A repeated submission must be refused.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->getStatusCode());
        }
        self::assertSame(1, DB::table('op_visit')->count());
    }

    public function test_legacy_values_survive_and_only_web_visits_are_deleted(): void
    {
        $this->makeRehab();
        $this->rehab->save($this->account, $this->project, $this->input(), $this->version());
        DB::table('op_rehabvisit')->update(['c_Ans1_Value' => 7, 'c_Ans4' => 'legacy text']);
        $this->rejected('q1_value', fn () => $this->rehab->save($this->account, $this->project, $this->input(['q1_value' => '7']), $this->version()));

        DB::table('account_city_capabilities')->where('capability', 'visits.delete')->update(['revoked_at' => now()]);
        try {
            $this->rehab->delete($this->account, $this->project, $this->version());
            self::fail('Deleting needs its grant.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        DB::table('account_city_capabilities')->where('capability', 'visits.delete')->update(['revoked_at' => null]);
        $this->rehab->delete($this->account, $this->project, $this->version());
        self::assertSame(0, DB::table('op_visit')->count());
        self::assertSame(0, DB::table('op_rehabvisit')->count());

        $legacy = ApplicationFixture::row('visit', 1, ['c_Kind' => 'R-cl', 'c_Date' => '2020-01-01'], ['MicroProject_ID' => $this->project]);
        FieldFixture::markImported($legacy, 'op_visit');
        $this->rejected('delete', fn () => $this->rehab->delete($this->account, $this->project, $this->version()));
        self::assertSame(1, DB::table('op_visit')->count());
    }

    public function test_pages_render_in_both_languages_and_grants_gate_them(): void
    {
        $this->makeRehab();
        $this->actingAs($this->account);
        $base = '/en/applications/'.$this->project.'/rehab-visit';
        $this->get('/en/applications/'.$this->project.'/visits')->assertOk()->assertSee('Rehabilitation closing visit');
        $this->get($base)->assertOk()->assertSee('Tiles and concrete')->assertSee('Save');
        $this->get('/ar/applications/'.$this->project.'/rehab-visit')->assertOk()->assertSee('dir="rtl"', false)->assertSee('بلاط + بيتون');
        $post = $this->input() + ['version' => $this->version()];
        $this->post($base, $post)->assertRedirect();
        $this->post($base, $post)->assertStatus(409);
        $this->post($base, ['q4_value' => '', 'version' => $this->version()] + $this->input())->assertSessionHasErrors('q4_value');
        $this->get($base)->assertOk()->assertSee('exact  note')->assertSee('Confirm deletion');

        DB::table('account_city_capabilities')->where('capability', 'visits.manage')->update(['revoked_at' => now()]);
        $this->get($base)->assertOk()->assertDontSee('>Save<', false);
        $this->post($base, ['version' => $this->version()] + $this->input())->assertForbidden();
        DB::table('account_city_capabilities')->where('capability', 'visits.view')->update(['revoked_at' => now()]);
        $this->get($base)->assertForbidden();
        self::assertSame(1, DB::table('op_visit')->count());
    }
}
