<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Assessments\ChecklistService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class ChecklistWorkflowTest extends TestCase
{
    private WebAccount $account;

    private ChecklistService $checklists;

    private array $intake;

    private array $members;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->checklists = app(ChecklistService::class);
        $this->intake = [1 => ApplicationFixture::intake(1), 2 => ApplicationFixture::intake(2)];
        $this->members = [1 => ApplicationFixture::checklistCatalog(1), 2 => ApplicationFixture::checklistCatalog(2)];
        $this->project = app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'Synthetic', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[1]['person'], $this->intake[1]['references']);
    }

    public function test_first_save_creates_the_person_checklist_with_in_place_member_rows(): void
    {
        $person = $this->intake[1]['person'];
        $this->save(['c_Notes' => "  exact\nnotes ", 'c_TeamDate' => '2026-10-20', 'c_HasHomeVisit' => '1', 'c_HVisit_Time' => '10:30'], ['HVisit_Visitor1_ID' => $this->members[1]['t']], [
            ['location' => 'k', 'member_id' => $this->members[1]['k'], 'opinion' => '1', 'name_notes' => 'Name', 'role_notes' => 'Role', 'notes' => 'key'],
            ['location' => 't', 'member_id' => $this->members[1]['t'], 'opinion' => '0', 'notes' => 'against'],
            ['location' => 'c', 'member_id' => $this->members[1]['c'], 'notes' => 'no opinion'],
        ]);
        $view = $this->checklists->show($this->account, $this->project, $person);
        self::assertSame("  exact\nnotes ", $view['header']->c_Notes);
        self::assertSame('2026-10-20', $view['header']->c_TeamDate);
        self::assertSame('10:30', $view['header']->c_HVisit_Time);
        self::assertSame(['k' => -1, 't' => 0, 'c' => -1], $view['members']->mapWithKeys(fn ($row) => [$row->c_Location => (int) $row->c_Opinion])->all());
        self::assertNotNull($view['members']->firstWhere('c_Location', 'k')->c_created_at);
        self::assertSame('Name', $view['members']->firstWhere('c_Location', 'k')->c_NameNotes);
    }

    public function test_desktop_section_rules_are_enforced_and_switched_off_details_are_cleared(): void
    {
        $this->assertStatus(422, fn () => $this->save(['c_HasHomeVisit' => '1']));
        $this->assertStatus(422, fn () => $this->save(['c_HasProjectVisit' => '1']));
        $this->assertStatus(422, fn () => $this->save(['c_HasProTest' => '1', 'c_ProTest_Note' => 'n']));
        $this->assertStatus(422, fn () => $this->save(['c_HasProTrain' => '1', 'c_ProTrain_Trainer' => 't', 'c_ProTrain_Place' => 'p']));
        $this->assertStatus(422, fn () => $this->save(['c_HasMngTrain' => '1', 'c_MngTrain_Trainer' => 't']));
        $this->assertStatus(422, fn () => $this->save(['c_RejectMsg' => '1']));
        $this->assertStatus(422, fn () => $this->save(['c_NeedProTest' => '2']));
        $this->assertStatus(422, fn () => $this->save(['c_TeamDate' => '20/10/2026']));
        self::assertSame(0, DB::table('op_checklist')->count());
        $this->save(['c_HasProTest' => '1', 'c_ProTest_Note' => 'n', 'c_ProTest_Result' => '1', 'c_HasProTrain' => '-1'], ['ProTest_User_ID' => $this->members[1]['t']]);
        $this->save(['c_HasProTest' => '0']);
        $header = $this->checklists->show($this->account, $this->project, $this->intake[1]['person'])['header'];
        self::assertSame(0, (int) $header->c_HasProTest);
        self::assertNull($header->c_ProTest_Note);
        self::assertNull($header->c_ProTest_Result);
        self::assertNull($header->ref_2ebfabb9e04b_id);
        self::assertSame(-1, (int) $header->c_HasProTrain);
    }

    public function test_members_must_match_their_list_city_and_project_beneficiary(): void
    {
        $this->assertStatus(422, fn () => $this->save([], [], [['location' => 'k', 'member_id' => $this->members[1]['t']]]));
        $this->assertStatus(422, fn () => $this->save([], [], [['location' => 't', 'member_id' => $this->members[2]['t']]]));
        $this->assertStatus(422, fn () => $this->save([], ['HVisit_Visitor1_ID' => $this->members[2]['t']]));
        $this->assertStatus(422, fn () => $this->save([], [], [['location' => 't', 'member_id' => $this->members[1]['t'], 'opinion' => '5']]));
        $stranger = ApplicationFixture::row('person', 1, ['c_P_FirstName' => 'Other']);
        $this->assertStatus(422, fn () => $this->save([], [], [], $stranger));
        $this->assertStatus(404, fn () => $this->checklists->show($this->account, $this->project, $stranger));
        self::assertSame(0, DB::table('op_person_checklistmembers')->count());
        self::assertSame(0, DB::table('op_checklist')->count());
    }

    public function test_existing_members_are_edited_in_place_without_moving_lists_or_rewriting_unchanged_rows(): void
    {
        $this->save(['c_Notes' => 'a'], [], [['location' => 't', 'member_id' => $this->members[1]['t'], 'opinion' => '1', 'notes' => 'x']]);
        $row = DB::table('op_person_checklistmembers')->first();
        DB::table('op_person_checklistmembers')->where('id', $row->id)->update(['c_created_at' => '2020-01-01 00:00:00']);
        $this->save([], [], [['id' => $row->id, 'location' => 't', 'member_id' => $this->members[1]['t'], 'opinion' => '1', 'notes' => 'x']]);
        self::assertSame('2020-01-01 00:00:00', DB::table('op_person_checklistmembers')->where('id', $row->id)->value('c_created_at'));
        $this->save([], [], [['id' => $row->id, 'location' => 't', 'member_id' => $this->members[1]['t2'], 'opinion' => '0', 'notes' => 'y']]);
        $changed = DB::table('op_person_checklistmembers')->where('id', $row->id)->first();
        self::assertSame('y', $changed->c_Notes);
        self::assertSame('2020-01-01 00:00:00', $changed->c_created_at);
        $this->assertStatus(422, fn () => $this->save([], [], [['id' => $row->id, 'location' => 'c', 'member_id' => $this->members[1]['c']]]));
        $this->assertStatus(422, fn () => $this->save([], [], [['id' => 999, 'location' => 't', 'member_id' => $this->members[1]['t']]]));
        self::assertSame(1, DB::table('op_person_checklistmembers')->count());
    }

    public function test_untouched_legacy_values_survive_and_stale_or_ambiguous_saves_are_refused(): void
    {
        $person = $this->intake[1]['person'];
        $legacy = ApplicationFixture::row('checklist', 1, ['c_Notes' => '  legacy  ', 'c_TeamDate' => '0000-00-00', 'c_HasProTest' => 7], ['Person_ID' => $person]);
        $this->save(['c_VisitsReport' => 'report']);
        $header = DB::table('op_checklist')->where('id', $legacy)->first();
        self::assertSame('  legacy  ', $header->c_Notes);
        self::assertSame('0000-00-00', $header->c_TeamDate);
        self::assertSame(7, (int) $header->c_HasProTest);
        self::assertSame('report', $header->c_VisitsReport);
        $stale = $this->checklists->show($this->account, $this->project, $person)['version'];
        $this->save(['c_Notes' => 'newer']);
        $this->assertStatus(409, fn () => $this->checklists->save($this->account, $this->project, $person, null, ['c_Notes' => 'lost'], [], [], $stale));
        ApplicationFixture::row('checklist', 1, ['c_Notes' => 'second'], ['Person_ID' => $person]);
        $this->assertStatus(422, fn () => $this->save(['c_Notes' => 'which one']));
        self::assertTrue($this->checklists->show($this->account, $this->project, $person)['needsHeaderChoice']);
        $this->save(['c_Notes' => 'chosen'], [], [], null, $legacy);
        self::assertSame('chosen', DB::table('op_checklist')->where('id', $legacy)->value('c_Notes'));
        self::assertSame('second', DB::table('op_checklist')->where('id', '<>', $legacy)->value('c_Notes'));
    }

    public function test_grants_gate_the_checklist_per_city_and_capability(): void
    {
        DB::table('account_city_capabilities')->where('capability', 'assessments.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->save(['c_Notes' => 'x']));
        self::assertFalse($this->checklists->show($this->account, $this->project, $this->intake[1]['person'])['canManage']);
        DB::table('account_city_capabilities')->where('capability', 'assessments.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->checklists->show($this->account, $this->project, $this->intake[1]['person']));
        self::assertSame(0, DB::table('op_checklist')->count());
    }

    private function save(array $fields = [], array $references = [], array $members = [], ?int $person = null, ?int $header = null): int
    {
        $person ??= $this->intake[1]['person'];
        $version = $person === $this->intake[1]['person'] ? $this->checklists->show($this->account, $this->project, $person)['version'] : str_repeat('0', 64);

        return $this->checklists->save($this->account, $this->project, $person, $header, $fields, $references, $members, $version);
    }

    private function assertStatus(int $status, callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected status '.$status);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        } catch (ValidationException $exception) {
            self::assertSame($status, $exception->status);
        }
    }
}
