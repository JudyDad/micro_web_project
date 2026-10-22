<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\VisitScheduleService;
use App\Services\Field\VisitService;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class VisitWorkflowTest extends TestCase
{
    private WebAccount $account;

    private VisitService $visits;

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
        $this->visits = app(VisitService::class);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FieldFixture::visitCatalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FieldFixture::assignVisitTasks($this->project, $this->catalog);
    }

    private function version(?int $project = null): string
    {
        return $this->visits->hub($this->account, $project ?? $this->project)['version'];
    }

    private function opening(array $overrides = []): array
    {
        return $overrides + [
            'date' => '2026-10-20', 'indicators' => ' exact  indicators ', 'street_id' => $this->catalog['street'], 'place_note' => 'Near the market',
            'q1_value' => '1', 'q1_text' => 'On time', 'q2_value' => '0', 'q2_text' => 'Delays', 'q3_value' => '1', 'q4_value' => '3', 'q4_text' => '75 percent',
            'q5_value' => '1', 'q6_value' => '0', 'q7_value' => '', 'q7_text' => 'Not tried yet',
            'office1_value' => '2', 'office1_text' => 'Kind staff', 'office6_value' => '0', 'other_comments' => 'More chairs',
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

    public function test_an_opening_visit_is_recorded_exactly_and_side_effects_run_once(): void
    {
        $id = $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version());
        $row = DB::table('op_visit')->where('id', $id)->first();
        self::assertSame('O-op', $row->c_Kind);
        self::assertSame(' exact  indicators ', $row->c_Indicators);
        self::assertSame('synthetic.applications', $row->c_Created_By);
        self::assertNull($row->c_Edited_By);
        self::assertSame([1, 0, 1, 3, 1, 0, -1], array_map(fn ($n) => (int) $row->{'c_Ans'.$n.'_Value'}, range(1, 7)));
        self::assertSame('Not tried yet', $row->c_Ans7);
        $office = DB::table('op_office_monitoring')->first();
        self::assertSame([2, -1, -1, -1, -1, 0], array_map(fn ($n) => (int) $office->{'c_Ans'.$n.'_Value'}, range(1, 6)));
        self::assertSame('More chairs', $office->c_OtherComments);
        $project = DB::table('op_microproject')->where('id', $this->project)->first();
        self::assertSame('Near the market', $project->c_MP_AddressAfterFund);
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][25])->value('c_State'));
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][31])->value('c_State'));

        $this->assertHttpStatus(409, fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), str_repeat('0', 64)));
        $this->visits->save($this->account, $this->project, 'O-op', $this->opening(['q1_text' => 'Changed']), $this->version());
        self::assertSame(1, DB::table('op_visit')->count());
        self::assertSame(1, DB::table('op_office_monitoring')->count());
        self::assertSame('Changed', DB::table('op_visit')->value('c_Ans1'));
        self::assertSame('synthetic.applications', DB::table('op_visit')->value('c_Edited_By'));
    }

    public function test_validation_and_kind_rules_write_nothing(): void
    {
        $this->rejected('q1_value', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(['q1_value' => '7']), $this->version()));
        $this->rejected('q4_value', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(['q4_value' => '5']), $this->version()));
        $this->rejected('date', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(['date' => '2026-13-01']), $this->version()));
        $this->rejected('street_id', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(['street_id' => 9999]), $this->version()));
        $this->rejected('visit', fn () => $this->visits->save($this->account, $this->project, 'V-op', $this->opening(['vehicle' => 'ABC']), $this->version()));
        self::assertSame(0, DB::table('op_visit')->count());
        self::assertNull(DB::table('op_microproject')->where('id', $this->project)->value('c_MP_AddressAfterFund'));

        // A legacy visit of the other opening kind already takes the phase.
        ApplicationFixture::row('visit', 1, ['c_Kind' => 'V-op', 'c_Date' => '2020-01-01', 'c_VehicleNumber' => 'OLD'], ['MicroProject_ID' => $this->project]);
        $this->rejected('visit', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version()));
        self::assertSame(1, DB::table('op_visit')->count());
    }

    public function test_vehicle_projects_use_the_taxi_questionnaire_and_need_a_plate_number(): void
    {
        DB::table('op_subcategory')->where('id', $this->intake['references']['SubCategory_ID'])->update(['c_ID' => 1]);
        $input = $this->opening(['q3_value' => '4', 'q4_value' => '1', 'q7_text' => 'ignored', 'q7_value' => '1']);
        unset($input['street_id'], $input['place_note']);
        $this->rejected('vehicle', fn () => $this->visits->save($this->account, $this->project, 'V-op', $input, $this->version()));
        $this->rejected('visit', fn () => $this->visits->save($this->account, $this->project, 'O-op', $input, $this->version()));
        $id = $this->visits->save($this->account, $this->project, 'V-op', $input + ['vehicle' => 'ABC 123'], $this->version());
        $row = DB::table('op_visit')->where('id', $id)->first();
        self::assertSame('ABC 123', $row->c_VehicleNumber);
        self::assertSame(-1, (int) $row->c_Ans7_Value, 'the unused seventh slot keeps the desktop default');
        self::assertSame('', $row->c_Ans7);
        self::assertNull(DB::table('op_microproject')->where('id', $this->project)->value('c_MP_AddressAfterFund'), 'taxi visits do not touch the place');
    }

    public function test_a_closing_visit_finishes_the_project_once_and_uses_its_own_choices(): void
    {
        $closing = ['date' => '2026-12-01', 'q1_value' => '0', 'q2_value' => '3', 'q3_value' => '2', 'q4_text' => 'Indirect', 'q5_value' => '3', 'q6_text' => 'Hope', 'office1_value' => '1', 'office4_value' => '2', 'indicators' => 'Fine'];
        $this->rejected('q3_value', fn () => $this->visits->save($this->account, $this->project, 'O-cl', ['q3_value' => '3'] + $closing, $this->version()));
        $this->visits->save($this->account, $this->project, 'O-cl', $closing, $this->version());
        $row = DB::table('op_visit')->first();
        self::assertSame([0, 3, 2, -1, 3, -1, -1], array_map(fn ($n) => (int) $row->{'c_Ans'.$n.'_Value'}, range(1, 7)));
        $office = DB::table('op_office_monitoring')->first();
        self::assertSame([1, -1, -1, 2, -1, -1], array_map(fn ($n) => (int) $office->{'c_Ans'.$n.'_Value'}, range(1, 6)));
        self::assertSame(1, DB::table('op_microproject_state')->where($this->stateRef(), $this->intake['states']['Finished'])->count());
        $this->visits->save($this->account, $this->project, 'O-cl', $closing + ['q4_text' => 'Edited'], $this->version());
        self::assertSame(1, DB::table('op_microproject_state')->where($this->stateRef(), $this->intake['states']['Finished'])->count());
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][31])->value('c_State'));
        $this->assertHttpStatus(404, fn () => $this->visits->form($this->account, $this->project, 'V-cl'));
    }

    private function stateRef(): string
    {
        return app(OperationalTables::class)->relation('microproject_state', 'State_ID')['resolved_column'];
    }

    public function test_legacy_values_survive_an_unrelated_edit(): void
    {
        $id = $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version());
        DB::table('op_visit')->where('id', $id)->update(['c_Ans1_Value' => 9, 'c_Ans7' => 'legacy seventh', 'c_Date' => '1999-01-01']);
        DB::table('op_office_monitoring')->update(['c_Ans1_Value' => 5]);
        $input = $this->opening(['date' => '1999-01-01', 'q1_value' => '9', 'q7_text' => 'legacy seventh', 'office1_value' => '5', 'indicators' => 'Only this changed']);
        $this->rejected('q1_value', fn () => $this->visits->save($this->account, $this->project, 'O-op', ['q1_value' => '8'] + $input, $this->version()));
        $this->visits->save($this->account, $this->project, 'O-op', $input, $this->version());
        $row = DB::table('op_visit')->where('id', $id)->first();
        self::assertSame(9, (int) $row->c_Ans1_Value);
        self::assertSame('legacy seventh', $row->c_Ans7);
        self::assertSame('Only this changed', $row->c_Indicators);
        self::assertSame(5, (int) DB::table('op_office_monitoring')->value('c_Ans1_Value'));
    }

    public function test_rehabilitation_grant_projects_have_no_opening_or_closing_visit(): void
    {
        $type = $this->intake['references']['MP_Type_ID'];
        DB::table('op_microprojecttype')->where('id', $type)->update(['c_Name' => 'ترميم منزل']);
        $fund = ApplicationFixture::row('fundtype', 1, ['c_ID' => 3, 'c_Name' => 'منحة', 'c_Name_en' => 'Grant']);
        DB::table('op_microproject')->where('id', $this->project)->update(app(OperationalTables::class)->link('microproject', 'MP_FundType_ID', $fund));
        $this->rejected('visit', fn () => $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version()));
        self::assertTrue($this->visits->hub($this->account, $this->project)['rehab']);
        self::assertSame([], $this->visits->hub($this->account, $this->project)['available']);
    }

    public function test_pages_render_and_grants_gate_them(): void
    {
        $this->actingAs($this->account);
        $base = '/en/applications/'.$this->project.'/visits';
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Visits');
        $this->get($base)->assertOk()->assertSee('Opening visit (general project)')->assertSee('Closing visit (general project)');
        $this->get($base.'/O-op')->assertOk()->assertSee('Did you keep to the expected opening period')->assertSee('Save');
        $this->get('/ar/applications/'.$this->project.'/visits/O-op')->assertOk()->assertSee('dir="rtl"', false)->assertSee('هل التزمت بالفترة المتوقعة');
        $this->get($base.'/V-op')->assertNotFound();
        $this->get($base.'/X-op')->assertNotFound();
        $post = $this->opening() + ['version' => $this->version()];
        $this->post($base.'/O-op', $post)->assertRedirect();
        $this->post($base.'/O-op', $post)->assertStatus(409);
        $this->post($base.'/O-op', ['version' => $this->version(), 'q1_value' => 'x'] + $this->opening())->assertSessionHasErrors('q1_value');
        $this->get($base.'/O-op')->assertOk()->assertSee('On time')->assertSee('Confirm deletion');
        $this->get($base)->assertOk()->assertSee('2026-10-20')->assertSee('Closing visit (general project)');

        DB::table('account_city_capabilities')->where('capability', 'visits.manage')->update(['revoked_at' => now()]);
        $this->get($base.'/O-op')->assertOk()->assertDontSee('>Save<', false);
        $this->post($base.'/O-op', ['version' => $this->version()] + $this->opening())->assertForbidden();
        DB::table('account_city_capabilities')->where('capability', 'visits.view')->update(['revoked_at' => now()]);
        $this->get($base)->assertForbidden();
        $this->get('/en/visit-schedule')->assertForbidden();
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('>Visits<', false);
        self::assertSame(1, DB::table('op_visit')->count());
    }

    public function test_the_schedule_lists_visit_notices_only_for_permitted_cities_and_reports_done_visits(): void
    {
        $other = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(2), 2);
        $notice = fn (int $city, int $project, string $body, string $date, array $fields = []) => ApplicationFixture::row('user_notification', $city, $fields + ['c_Pay_ID' => -21, 'c_Body' => $body, 'c_Date' => $date, 'c_P_Name' => 'Synthetic beneficiary', 'c_Seen' => 0, 'c_MicroProject_ID' => 1000], ['MicroProject_ID' => $project]);
        $notice(1, $this->project, 'Visit Opening-O', '2026-11-05');
        $notice(1, $this->project, 'Visit Closing-O', '2026-12-05', ['c_Seen' => 1]);
        $notice(1, $this->project, 'Visit M1-O', '2027-01-05');
        $notice(1, $this->project, 'Some other text', '2027-02-05');
        $notice(1, $this->project, 'Payment due', '2026-11-06', ['c_Pay_ID' => 55]);
        $notice(2, $other, 'Visit Opening-V', '2026-11-07');
        $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version());

        $schedule = app(VisitScheduleService::class);
        $all = $schedule->schedule($this->account, [])['rows']->getCollection()->keyBy('c_Body');
        self::assertEqualsCanonicalizing(['Visit Opening-O', 'Visit Closing-O', 'Visit M1-O', 'Some other text', 'Visit Opening-V'], $all->keys()->all());
        self::assertSame('done', $all['Visit Opening-O']->visit_status);
        self::assertNull($all['Visit Closing-O']->visit_status);
        self::assertNull($all['Visit M1-O']->visit_status);
        self::assertSame('na', $all['Some other text']->visit_status);

        self::assertSame(['Visit Opening-V'], $schedule->schedule($this->account, ['kind' => 'V'])['rows']->pluck('c_Body')->all());
        self::assertSame(['Visit Closing-O'], $schedule->schedule($this->account, ['seen' => '1'])['rows']->pluck('c_Body')->all());
        self::assertSame(['Visit M1-O'], $schedule->schedule($this->account, ['number' => 'M1'])['rows']->pluck('c_Body')->all());
        self::assertSame(['Visit Closing-O'], $schedule->schedule($this->account, ['months' => [12]])['rows']->pluck('c_Body')->all());
        self::assertSame(['Visit M1-O'], $schedule->schedule($this->account, ['year' => 2027, 'months' => [1]])['rows']->pluck('c_Body')->all());

        DB::table('account_city_capabilities')->where('city_id', 2)->where('capability', 'visits.view')->update(['revoked_at' => now()]);
        self::assertNotContains('Visit Opening-V', $schedule->schedule($this->account, ['cities' => [1, 2]])['rows']->pluck('c_Body')->all());
        $this->actingAs($this->account);
        $this->get('/en/visit-schedule')->assertOk()->assertSee('Visit schedule')->assertSee('Visit Opening-O')->assertDontSee('Visit Opening-V');
        $this->get('/ar/visit-schedule?year=2026&months[]=11')->assertOk()->assertSee('dir="rtl"', false);
    }

    public function test_a_web_visit_is_deleted_with_its_office_row_and_task_reset_and_imported_ones_are_kept(): void
    {
        $this->visits->save($this->account, $this->project, 'O-op', $this->opening(), $this->version());
        DB::table('account_city_capabilities')->where('capability', 'visits.delete')->update(['revoked_at' => now()]);
        $this->assertHttpStatus(403, fn () => $this->visits->delete($this->account, $this->project, 'O-op', $this->version()));
        DB::table('account_city_capabilities')->where('capability', 'visits.delete')->update(['revoked_at' => null]);
        $this->assertHttpStatus(409, fn () => $this->visits->delete($this->account, $this->project, 'O-op', str_repeat('0', 64)));
        $this->visits->delete($this->account, $this->project, 'O-op', $this->version());
        self::assertSame(0, DB::table('op_visit')->count());
        self::assertSame(0, DB::table('op_office_monitoring')->count());
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('ref_bbc62ceac64e_id', $this->catalog['tasks'][25])->value('c_State'));
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'visits.deleted')->count());
        $this->assertHttpStatus(404, fn () => $this->visits->delete($this->account, $this->project, 'O-op', $this->version()));

        $legacy = ApplicationFixture::row('visit', 1, ['c_Kind' => 'O-cl', 'c_Date' => '2020-01-01'], ['MicroProject_ID' => $this->project]);
        FieldFixture::markImported($legacy, 'op_visit');
        $this->rejected('delete', fn () => $this->visits->delete($this->account, $this->project, 'O-cl', $this->version()));
        self::assertSame(1, DB::table('op_visit')->count());
    }
}
