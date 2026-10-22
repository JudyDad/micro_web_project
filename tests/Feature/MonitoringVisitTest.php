<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Field\MonitoringVisitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class MonitoringVisitTest extends TestCase
{
    private WebAccount $account;

    private MonitoringVisitService $me;

    private array $intake;

    private array $catalog;

    private int $project;

    private int $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        FieldFixture::grant($this->account);
        $this->me = app(MonitoringVisitService::class);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FieldFixture::meCatalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        $this->person = $this->intake['person'];
        FieldFixture::assignVisitTasks($this->project, ['tasks' => $this->catalog['tasks']]);
    }

    private function version(?int $project = null): string
    {
        return $this->me->hub($this->account, $project ?? $this->project)['version'];
    }

    private function input(array $overrides = []): array
    {
        [$q1, $q2] = $this->catalog['questions'];

        return $overrides + [
            'number' => '1', 'date' => '2026-11-02', 'profit' => '1,250.5', 'ans1' => ' exact  summary ', 'indicators' => 'None', 'notes' => 'ok',
            'a'.$q1['id'] => $q1['answers']['Yes'], 'n'.$q1['id'] => 'Regular', 'a'.$q2['id'] => $q2['answers']['No'], 'n'.$q2['id'] => '',
        ];
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

    private function assertHttpStatus(int $expected, callable $call): void
    {
        try {
            $call();
            self::fail('Expected HTTP '.$expected);
        } catch (HttpException $e) {
            self::assertSame($expected, $e->getStatusCode());
        }
    }

    public function test_a_visit_is_recorded_with_its_answers_result_and_task_and_edited_in_place(): void
    {
        $hub = $this->me->hub($this->account, $this->project);
        self::assertTrue($hub['creatable']);
        self::assertSame(1, $hub['people']->first()->next);

        $id = $this->me->save($this->account, $this->project, $this->person, null, $this->input(), $this->version());
        $row = DB::table('op_mevisit')->where('id', $id)->first();
        self::assertSame('o', $row->c_Kind);
        self::assertSame('1', $row->c_Number);
        self::assertEquals(1250.5, (float) $row->c_Profit);
        self::assertSame(' exact  summary ', $row->c_Ans1);
        self::assertSame('synthetic.applications', $row->c_Created_By);
        self::assertSame(2, DB::table('op_mevisit_answer')->count());
        self::assertSame(5 + 1 + 4, (int) $row->c_Result, 'answer marks 5 and 1 plus the 101–1,000,000 profit mark 4');
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][26])->value('c_State'));
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][27])->value('c_State'));

        [$q1] = $this->catalog['questions'];
        $this->me->save($this->account, $this->project, $this->person, $id, $this->input(['a'.$q1['id'] => $q1['answers']['No'], 'profit' => '50']), $this->version());
        self::assertSame(1, DB::table('op_mevisit')->count());
        self::assertSame(2, DB::table('op_mevisit_answer')->count());
        self::assertSame(2 + 1 + 1, (int) DB::table('op_mevisit')->value('c_Result'));
        self::assertSame('synthetic.applications', DB::table('op_mevisit')->value('c_Edited_By'));
        self::assertSame(2, $this->me->hub($this->account, $this->project)['people']->first()->next);
    }

    public function test_validation_and_eligibility_rules_write_nothing(): void
    {
        [$q1, $q2] = $this->catalog['questions'];
        $this->rejected('answers', fn () => $this->me->save($this->account, $this->project, $this->person, null, array_diff_key($this->input(), ['a'.$q2['id'] => 1]), $this->version()));
        $this->rejected('a'.$q1['id'], fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['a'.$q1['id'] => $q2['answers']['Yes']]), $this->version()));
        $this->rejected('number', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['number' => '0']), $this->version()));
        $this->rejected('number', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['number' => '100']), $this->version()));
        $this->rejected('profit', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['profit' => '']), $this->version()));
        $this->rejected('profit', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['profit' => '-5']), $this->version()));
        $this->rejected('date', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['date' => '2026-02-30']), $this->version()));
        self::assertSame(0, DB::table('op_mevisit')->count());
        self::assertSame(0, DB::table('op_mevisit_answer')->count());

        $this->me->save($this->account, $this->project, $this->person, null, $this->input(), $this->version());
        $this->rejected('number', fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(), $this->version()));
        self::assertSame(1, DB::table('op_mevisit')->count());
        $this->assertHttpStatus(409, fn () => $this->me->save($this->account, $this->project, $this->person, null, $this->input(['number' => '2']), str_repeat('0', 64)));

        $unfunded = app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'Unfunded', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake['person'], $this->intake['references']);
        $this->assertHttpStatus(404, fn () => $this->me->form($this->account, $unfunded, $this->person, null));
        self::assertFalse($this->me->hub($this->account, $unfunded)['creatable']);
    }

    public function test_vehicle_projects_ask_the_vehicle_questions(): void
    {
        DB::table('op_subcategory')->where('id', $this->intake['references']['SubCategory_ID'])->update(['c_ID' => 2]);
        $vehicle = $this->catalog['vehicle'];
        $form = $this->me->form($this->account, $this->project, $this->person, null);
        self::assertSame('v', $form['kind']);
        self::assertSame([$vehicle['id']], $form['questions']->pluck('id')->all());
        $id = $this->me->save($this->account, $this->project, $this->person, null, ['number' => '1', 'profit' => '10', 'a'.$vehicle['id'] => $vehicle['answers']['Yes']], $this->version());
        self::assertSame('v', DB::table('op_mevisit')->where('id', $id)->value('c_Kind'));
        self::assertSame(6 + 1, (int) DB::table('op_mevisit')->where('id', $id)->value('c_Result'));
    }

    public function test_legacy_values_and_unrelated_answers_survive_an_edit(): void
    {
        $id = $this->me->save($this->account, $this->project, $this->person, null, $this->input(), $this->version());
        $stray = ApplicationFixture::row('mequestion', 1, ['c_ID' => 99, 'c_Name' => 'Retired', 'c_Type' => 'x']);
        $strayAnswer = ApplicationFixture::row('meanswer', 1, ['c_ID' => 990, 'c_Name' => 'Old', 'c_Mark' => 7], ['Question_ID' => $stray]);
        ApplicationFixture::row('mevisit_answer', 1, ['c_Notes' => 'old note'], ['Visit_ID' => $id, 'Question_ID' => $stray, 'Answer_ID' => $strayAnswer]);
        DB::table('op_mevisit')->where('id', $id)->update(['c_Date' => '1999-01-01', 'c_Profit' => 77.25]);
        $this->me->save($this->account, $this->project, $this->person, $id, $this->input(['date' => '1999-01-01', 'profit' => '77.25', 'notes' => 'Only this changed']), $this->version());
        $row = DB::table('op_mevisit')->where('id', $id)->first();
        self::assertSame('Only this changed', $row->c_Notes);
        self::assertSame('1999-01-01', substr((string) $row->c_Date, 0, 10));
        self::assertSame(3, DB::table('op_mevisit_answer')->count(), 'the retired question\'s answer is untouched');
        self::assertSame(5 + 1 + 7 + 1, (int) $row->c_Result, 'the stored answer\'s mark counts, as the desktop sums stored answers');
    }

    public function test_a_web_visit_can_be_deleted_and_an_imported_one_is_kept(): void
    {
        $id = $this->me->save($this->account, $this->project, $this->person, null, $this->input(), $this->version());
        DB::table('account_city_capabilities')->where('capability', 'me_visits.delete')->update(['revoked_at' => now()]);
        $this->assertHttpStatus(403, fn () => $this->me->delete($this->account, $this->project, $id, $this->version()));
        DB::table('account_city_capabilities')->where('capability', 'me_visits.delete')->update(['revoked_at' => null]);
        $this->me->delete($this->account, $this->project, $id, $this->version());
        self::assertSame(0, DB::table('op_mevisit')->count());
        self::assertSame(0, DB::table('op_mevisit_answer')->count());
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][26])->value('c_State'));
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'me_visits.deleted')->count());

        $legacy = ApplicationFixture::row('mevisit', 1, ['c_Kind' => 'o', 'c_Number' => '9', 'c_Date' => '2020-01-01', 'c_Profit' => 5, 'c_Result' => 3], ['Person_ID' => $this->person]);
        FieldFixture::markImported($legacy, 'op_mevisit');
        $this->rejected('delete', fn () => $this->me->delete($this->account, $this->project, $legacy, $this->version()));
        self::assertSame(1, DB::table('op_mevisit')->count());
    }

    public function test_pages_render_in_both_languages_and_grants_gate_them(): void
    {
        $this->actingAs($this->account);
        $base = '/en/applications/'.$this->project.'/me-visits';
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Monitoring and evaluation visits');
        $this->get($base)->assertOk()->assertSee('Add visit 1');
        $this->get($base.'/new/'.$this->person)->assertOk()->assertSee('Is the work regular?')->assertSee('Net profit');
        $this->get('/ar/applications/'.$this->project.'/me-visits/new/'.$this->person)->assertOk()->assertSee('dir="rtl"', false)->assertSee('الربح الصافي');
        $post = ['version' => $this->version(), 'person_id' => $this->person] + $this->input();
        $this->post($base, $post)->assertRedirect();
        $this->post($base, $post)->assertStatus(409);
        $this->post($base, ['version' => $this->version(), 'person_id' => $this->person, 'visit_id' => DB::table('op_mevisit')->value('id')] + $this->input(['profit' => 'abc']))->assertSessionHasErrors('profit');
        $visit = DB::table('op_mevisit')->value('id');
        $this->get($base.'/'.$visit)->assertOk()->assertSee('Regular')->assertSee('Confirm deletion');
        $this->get($base)->assertOk()->assertSee('#1');
        $this->post($base.'/'.$visit.'/delete', ['version' => $this->version()])->assertRedirect();
        self::assertSame(0, DB::table('op_mevisit')->count());
        $this->post($base, ['version' => $this->version(), 'person_id' => $this->person] + $this->input())->assertRedirect();

        DB::table('account_city_capabilities')->where('capability', 'me_visits.manage')->update(['revoked_at' => now()]);
        $this->get($base)->assertOk()->assertDontSee('Add visit');
        $this->get($base.'/new/'.$this->person)->assertNotFound();
        $this->post($base, ['version' => $this->version(), 'person_id' => $this->person] + $this->input(['number' => '2']))->assertForbidden();
        DB::table('account_city_capabilities')->where('capability', 'me_visits.view')->update(['revoked_at' => now()]);
        $this->get($base)->assertForbidden();
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('Monitoring and evaluation visits');
        self::assertSame(1, DB::table('op_mevisit')->count());
    }
}
