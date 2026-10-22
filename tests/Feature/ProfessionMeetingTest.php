<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\ProfessionMeetingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class ProfessionMeetingTest extends TestCase
{
    private WebAccount $account;

    private ProfessionMeetingService $meetings;

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
        $this->meetings = app(ProfessionMeetingService::class);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FieldFixture::meetingCatalog(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
    }

    private function projectNumber(): string
    {
        return (string) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID');
    }

    private function input(array $overrides = []): array
    {
        $o1 = $this->catalog['options']['1'];
        $o4 = $this->catalog['options']['4'];
        $q = $this->catalog['questions'];

        return $overrides + [
            'career_id' => $this->intake['references']['Career_ID'], 'date' => '2026-11-10', 'place' => 'Community hall', 'invited' => '10', 'attendance' => '4',
            'oc' => [$o1[1] => '3', $o1[2] => '1'], 'os' => [$o4[1] => '1', $o4[3] => '1'], 'ot' => [$o4[3] => 'Tourists'],
            'tx' => [$q['5'] => ' Repairs and parts '],
            'cc' => [1 => '1', 2 => '3', 3 => '0'], 'cr' => [1 => 'Many shops', 2 => 'Some shops'],
            'season' => [['count' => '4', 'from' => 'May', 'to' => 'July', 'reason' => 'Peak demand']],
            'brands' => "Brand A\n\n Brand B ",
            'equipment' => [['spec' => 'Welding', 'items' => 'Welder, mask']],
            'challenge' => [['name' => 'Economy', 'count' => '4', 'solution' => 'Cheaper loans']],
            'part' => [$q['19'] => [['project' => $this->projectNumber(), 'name' => 'Synthetic Applicant', 'speciality' => 'Trainer']]],
            'attendees' => [['project' => $this->projectNumber(), 'name' => 'Synthetic Applicant', 'speciality' => 'Welder'], ['name' => 'Guest A'], ['name' => 'Guest B'], ['name' => 'Guest C']],
            'minutes' => [$this->catalog['sections'][1] => 'Axis one notes', $this->catalog['sections'][2] => 'Axis two notes'],
        ];
    }

    private function version(?int $meeting = null): string
    {
        return $this->meetings->form($this->account, $meeting, $meeting === null ? 1 : null)['version'];
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

    private function create(array $overrides = []): int
    {
        return $this->meetings->save($this->account, null, 1, $this->input($overrides), $this->version());
    }

    private function detailCounts(): array
    {
        return array_map(fn ($t) => DB::table('op_profession_meeting_'.$t)->count(), ['answer', 'option_answer', 'competition', 'season', 'brand', 'equipment', 'challenge', 'participant', 'attendee', 'minute']);
    }

    public function test_a_meeting_is_recorded_with_every_answer_type(): void
    {
        $id = $this->create();
        $row = DB::table('op_profession_meeting')->where('id', $id)->first();
        self::assertSame('Community hall', $row->c_Place);
        self::assertSame([10, 4, 6], [(int) $row->c_InvitedCount, (int) $row->c_AttendanceCount, (int) $row->c_AbsenceCount]);
        self::assertSame($this->intake['references']['SubCategory_ID'], (int) DB::table('op_profession_meeting')->value('ref_466792cb6a0d_id'));
        self::assertSame([1, 4, 3, 1, 2, 1, 1, 1, 4, 2], $this->detailCounts());
        self::assertSame('Repairs and parts', DB::table('op_profession_meeting_answer')->value('c_Value_Text'));
        $options = DB::table('op_profession_meeting_option_answer')->get();
        self::assertSame(3, (int) DB::table('op_profession_meeting_option_answer')->where('ref_26e9e9e4d45b_id', $this->catalog['options']['1'][1])->value('c_Rank_Value'));
        self::assertSame('Tourists', $options->firstWhere('c_Other_Text', 'Tourists')->c_Other_Text);
        self::assertSame(['Brand A', 'Brand B'], DB::table('op_profession_meeting_brand')->orderBy('c_Sort_Order')->pluck('c_Brand_Name')->all());
        self::assertSame(['منافسة شديدة', 'منافسة متوسطة', 'منافسة ضعيفة'], DB::table('op_profession_meeting_competition')->orderBy('c_Sort_Order')->pluck('c_Competition_Level')->all());
        $attendee = DB::table('op_profession_meeting_attendee')->orderBy('id')->first();
        self::assertSame($this->project, (int) DB::table('op_profession_meeting_attendee')->value('ref_be13b301bc72_id'), 'the project number is linked');
        self::assertSame($this->intake['person'], (int) DB::table('op_profession_meeting_attendee')->value('ref_38f968fabc93_id'), 'the person is linked when exactly one linked person has that name');
        self::assertSame('Welder', $attendee->c_Speciality);
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'profession_meetings.saved')->count());
    }

    public function test_the_desktop_validation_rules_apply_and_write_nothing(): void
    {
        $q = $this->catalog['questions'];
        $o1 = $this->catalog['options']['1'];
        $o4 = $this->catalog['options']['4'];
        $cases = [
            'career_id' => ['career_id' => ''], 'place' => ['place' => ''], 'invited' => ['invited' => '0'], 'attendance' => ['attendance' => ''], 'date' => ['date' => '2026-02-30'],
            'question'.$q['1'] => ['oc' => [$o1[1] => '1']],
            'question'.$q['4'] => ['os' => []],
            'question'.$q['5'] => ['tx' => [$q['5'] => '  ']],
            'competition' => ['cc' => [1 => '4', 2 => '0', 3 => '0'], 'cr' => []],
            'season' => ['season' => [['count' => '4', 'from' => 'May']]],
            'brands' => ['brands' => " \n "],
            'equipment' => ['equipment' => [['spec' => 'Welding']]],
            'challenge' => ['challenge' => [['name' => 'Economy', 'count' => '4']]],
            'attendees' => ['attendees' => [['name' => 'Only one']]],
            'minutes' => ['minutes' => [$this->catalog['sections'][1] => 'Only axis one']],
            'project' => ['attendees' => [['project' => '999999', 'name' => 'X'], ['name' => 'B'], ['name' => 'C'], ['name' => 'D']]],
        ];
        foreach ($cases as $field => $overrides) {
            $this->rejected($field, fn () => $this->create($overrides));
        }
        $this->rejected('attendance', fn () => $this->create(['attendance' => '11']));
        $this->rejected('question'.$q['4'], fn () => $this->create(['ot' => []]));
        self::assertSame(0, DB::table('op_profession_meeting')->count());
        self::assertSame(array_fill(0, 10, 0), $this->detailCounts());
    }

    public function test_edits_sync_rows_in_place_and_remove_only_what_was_removed(): void
    {
        $id = $this->create();
        $before = DB::table('op_profession_meeting_option_answer')->orderBy('id')->pluck('id')->all();
        $answer = DB::table('op_profession_meeting_answer')->value('id');
        $o1 = $this->catalog['options']['1'];

        $this->meetings->save($this->account, $id, null, $this->input(['place' => 'New hall', 'oc' => [$o1[1] => '4']]), $this->version($id));
        self::assertSame('New hall', DB::table('op_profession_meeting')->value('c_Place'));
        self::assertSame(1, DB::table('op_profession_meeting')->count());
        self::assertSame($answer, DB::table('op_profession_meeting_answer')->value('id'), 'unchanged rows keep their identity');
        $remaining = DB::table('op_profession_meeting_option_answer')->orderBy('id')->pluck('id')->all();
        self::assertCount(count($before) - 1, $remaining, 'the removed option answer is deleted');
        self::assertSame(4, (int) DB::table('op_profession_meeting_option_answer')->where('ref_26e9e9e4d45b_id', $o1[1])->value('c_Rank_Value'));
        self::assertSame(0, DB::table('operational_deletions')->count());
    }

    public function test_imported_detail_rows_are_hidden_not_destroyed_by_an_edit(): void
    {
        $id = $this->create();
        $o1 = $this->catalog['options']['1'];
        $row = DB::table('op_profession_meeting_option_answer')->where('ref_26e9e9e4d45b_id', $o1[2])->value('id');
        FieldFixture::markImported($row, 'op_profession_meeting_option_answer');
        $this->meetings->save($this->account, $id, null, $this->input(['invited' => '10', 'oc' => [$o1[1] => '4']]), $this->version($id));
        self::assertSame(1, DB::table('op_profession_meeting_option_answer')->where('id', $row)->count(), 'the imported row is still stored');
        self::assertSame(1, DB::table('operational_deletions')->where('entity_id', $row)->count());
        self::assertSame(1, DB::table('operational_origins')->where('entity_id', $row)->count());
    }

    public function test_stale_saves_are_refused_and_delete_and_restore_follow_the_rules(): void
    {
        $id = $this->create();
        $this->assertHttpStatus(409, fn () => $this->meetings->save($this->account, $id, null, $this->input(), str_repeat('0', 64)));
        DB::table('account_city_capabilities')->where('capability', 'profession_meetings.delete')->update(['revoked_at' => now()]);
        $this->assertHttpStatus(403, fn () => $this->meetings->delete($this->account, $id, $this->version($id)));
        DB::table('account_city_capabilities')->where('capability', 'profession_meetings.delete')->update(['revoked_at' => null]);
        $this->meetings->delete($this->account, $id, $this->version($id));
        self::assertSame(0, DB::table('op_profession_meeting')->count());
        self::assertSame(array_fill(0, 10, 0), $this->detailCounts());

        $legacy = ApplicationFixture::row('profession_meeting', 1, ['c_MeetingDate' => '2020-01-01', 'c_Place' => 'Old hall', 'c_InvitedCount' => 5, 'c_AttendanceCount' => 3, 'c_AbsenceCount' => 2]);
        FieldFixture::markImported($legacy, 'op_profession_meeting');
        $this->assertHttpStatus(403, fn () => $this->meetings->delete($this->account, $legacy, $this->version($legacy)));
        FieldFixture::grantImportedDelete($this->account);
        $this->meetings->delete($this->account, $legacy, $this->version($legacy));
        self::assertSame(1, DB::table('op_profession_meeting')->count(), 'an imported meeting is hidden, never removed');
        self::assertSame([$legacy], $this->meetings->deleted($this->account)->pluck('id')->all());
        $this->assertHttpStatus(404, fn () => $this->meetings->form($this->account, $legacy, null));
        $this->meetings->restore($this->account, $legacy);
        self::assertSame('Old hall', $this->meetings->form($this->account, $legacy, null)['meeting']->c_Place);
    }

    public function test_pages_render_in_both_languages_and_grants_gate_them(): void
    {
        $this->actingAs($this->account);
        $this->get('/en/profession-meetings')->assertOk()->assertSee('Profession meetings')->assertSee('New meeting');
        $this->get('/en/profession-meetings/new?city=1')->assertOk()->assertSee('How did you learn the trade?')->assertSee('Who are the customers?');
        $this->get('/ar/profession-meetings/new?city=1')->assertOk()->assertSee('dir="rtl"', false)->assertSee('محضر الاجتماع');
        $post = ['version' => $this->version(), 'city_id' => 1] + $this->input();
        $response = $this->post('/en/profession-meetings', $post);
        $response->assertRedirect();
        $id = (int) DB::table('op_profession_meeting')->value('id');
        $this->post('/en/profession-meetings', $post)->assertStatus(409);
        $this->post('/en/profession-meetings', ['version' => $this->version(), 'city_id' => 1, 'place' => ''] + $this->input())->assertSessionHasErrors('place');
        $this->get('/en/profession-meetings/'.$id)->assertOk()->assertSee('Community hall')->assertSee('Repairs and parts')->assertSee('Confirm deletion');
        $this->get('/en/profession-meetings')->assertOk()->assertSee('Synthetic career');
        self::assertSame(1, DB::table('op_profession_meeting')->count());

        DB::table('account_city_capabilities')->where('capability', 'profession_meetings.manage')->update(['revoked_at' => now()]);
        $this->get('/en/profession-meetings/new?city=1')->assertNotFound();
        $this->get('/en/profession-meetings/'.$id)->assertOk()->assertDontSee('>Save<', false);
        $this->post('/en/profession-meetings', ['version' => $this->version($id), 'meeting_id' => $id] + $this->input())->assertForbidden();
        DB::table('account_city_capabilities')->where('capability', 'profession_meetings.view')->update(['revoked_at' => now()]);
        $this->get('/en/profession-meetings')->assertForbidden();
        $this->get('/en/profession-meetings/'.$id)->assertForbidden();
    }
}
