<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Applications\FullApplicationReadiness;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FullApplicationFixture;
use Tests\TestCase;

/** Invented data only. Each case breaks one rule of an otherwise complete application. */
final class FullApplicationReadinessTest extends TestCase
{
    private WebAccount $account;

    private OperationalTables $tables;

    private int $project;

    private int $person;

    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->tables = app(OperationalTables::class);
        $intake = ApplicationFixture::intake(1);
        $this->person = $intake['person'];
        $this->project = app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'مشروع', 'c_MP_DateOfRequest' => '2026-10-19'], $this->person, $intake['references']);
        FullApplicationFixture::complete($this->project, $this->person, $intake);
    }

    public function test_a_complete_application_has_no_issues(): void
    {
        self::assertSame([], $this->issues());
    }

    public function test_each_broken_rule_is_reported_with_its_section_and_field(): void
    {
        $cases = [
            'name' => [fn () => $this->person(['c_P_FatherName' => '   ']), 'beneficiary', 'required', 'P_FatherName'],
            'national number' => [fn () => $this->person(['c_P_NationalNumber' => null]), 'beneficiary', 'required', 'P_NationalNumber'],
            'marital status' => [fn () => $this->person($this->tables->link('person', 'P_MaritalStatus_ID', null)), 'beneficiary', 'required', 'P_MaritalStatus_ID'],
            'priest' => [fn () => $this->person($this->tables->link('person', 'P_Priest_ID', null) + $this->tables->link('person', 'P_Priest2_ID', null)), 'beneficiary', 'required', 'Priest'],
            'contact' => [fn () => $this->person(['c_P_Mobile' => '', 'c_P_HomeTel' => null, 'c_P_Mobile2' => ' ']), 'beneficiary', 'required', 'contact'],
            'military' => [fn () => $this->person(['c_P_Sex' => 'ذكر', 'c_P_MilitaryService' => 'مسرح', 'c_P_MilitaryPeriod' => null, 'c_P_IsMilitaryInWar' => null]), 'beneficiary', 'choice', 'IsMilitaryInWar'],
            'story' => [fn () => $this->link(['c_P_GoalHow' => '']), 'story', 'required', 'P_GoalHow'],
            'work choice' => [fn () => $this->link(['c_P_HasWork' => -1]), 'work', 'choice', 'P_HasWork'],
            'work rows' => [fn () => DB::table('op_person_workexp')->delete(), 'work', 'required_rows', 'current'],
            'work place' => [fn () => DB::table('op_person_workexp')->where('c_W_Status', 'Yes')->update(['c_W_Place' => '']), 'work', 'required', 'W_Place'],
            'prior work cause' => [fn () => DB::table('op_person_workexp')->where('c_W_Status', 'NO')->update(['c_W_CauseOfLoseDescription' => null]), 'work', 'required', 'W_CauseOfLoseDescription'],
            'education rows' => [fn () => DB::table('op_person_education')->delete(), 'education', 'required_rows', 'education'],
            'course date' => [fn () => DB::table('op_person_course')->update(['c_Date' => null]), 'education', 'required', 'Date'],
            'place type' => [fn () => DB::table('op_microproject')->where('id', $this->project)->update($this->tables->link('microproject', 'MP_PlaceType_ID', null)), 'project', 'required', 'MP_PlaceType_ID'],
            'agreement' => [fn () => DB::table('op_microproject')->where('id', $this->project)->update(['c_MP_Agreement3' => -1]), 'project', 'choice', 'MP_Agreement3'],
            'budget rows' => [fn () => DB::table('op_material')->delete(), 'project', 'required_rows', 'material'],
            'budget price' => [fn () => DB::table('op_material')->update(['c_Price' => '']), 'project', 'required', 'Price'],
            'family number' => [fn () => DB::table('op_family')->update(['c_F_Number' => ' ']), 'family', 'required', 'FamilyNumber'],
            'living with family' => [fn () => $this->link(['c_P_IsLivingWithFamily' => null]), 'family', 'choice', 'P_IsLivingWithFamily'],
            'member national number' => [fn () => DB::table('op_person')->where('c_P_FirstName', 'Member')->update(['c_P_NationalNumber' => null]), 'family', 'required', 'P_NationalNumber'],
            'member specialization' => [fn () => DB::table('op_person_family')->where('c_HasWork', 9)->update($this->tables->link('person_family', 'Education_ID', null)), 'family', 'required', 'Education_ID'],
            'loss rows' => [fn () => DB::table('op_person_loss')->delete(), 'support', 'required_rows', 'loss'],
            'illness aid' => [fn () => DB::table('op_person_illness')->update(['c_Has_MedicalAid' => 1]), 'support', 'required', 'AidSource_ID'],
            'owned asset year' => [fn () => DB::table('op_person_asset')->update(['c_Year' => null]), 'support', 'required', 'Year'],
            'aid year' => [fn () => DB::table('op_person_aidsource')->update(['c_DateYear' => '']), 'support', 'required', 'DateYear'],
            'debts choice' => [fn () => $this->link(['c_P_HasDebts' => null]), 'support', 'choice', 'P_HasDebts'],
            'help rows' => [fn () => DB::table('op_person_help')->delete(), 'support', 'required_rows', 'help'],
            'home power rows' => [fn () => DB::table('op_person_powersource')->delete(), 'support', 'required_rows', 'homepower'],
            'home power note' => [fn () => DB::table('op_person_powersource')->update(['c_Note' => '']), 'support', 'required', 'Note'],
        ];
        foreach ($cases as $name => [$break, $section, $code, $field]) {
            DB::beginTransaction();
            try {
                $break();
                $issues = $this->issues();
                self::assertContains(['section' => $section, 'code' => $code, 'field' => $field], array_map(fn ($i) => ['section' => $i['section'], 'code' => $i['code'], 'field' => $i['field']], $issues), $name.': '.json_encode($issues));
            } finally {
                DB::rollBack();
            }
        }
        self::assertSame([], $this->issues());
    }

    public function test_not_working_prior_rows_and_not_educated_members_skip_dependent_fields(): void
    {
        $notWorking = ApplicationFixture::row('work', 1, ['c_W_ID' => 99, 'c_W_Name' => 'لا يعمل']);
        DB::table('op_person_workexp')->where('c_W_Status', 'NO')->update($this->tables->link('person_workexp', 'Work_ID', (int) $notWorking) + ['c_W_CauseOfLoseDescription' => null, 'c_W_Place' => null, 'c_W_PlaceState' => null] + $this->tables->link('person_workexp', 'W_WorkType_ID', null));
        $uneducated = ApplicationFixture::row('educationlevel', 1, ['c_ID' => 98, 'c_Name' => 'غير متعلّم']);
        DB::table('op_person_family')->where('c_HasWork', 9)->update($this->tables->link('person_family', 'EducationLevel_ID', $uneducated) + $this->tables->link('person_family', 'Education_ID', null));
        self::assertSame([], $this->issues());
    }

    public function test_rows_are_reported_by_identifier_and_nothing_is_written(): void
    {
        $row = (int) DB::table('op_material')->value('id');
        DB::table('op_material')->where('id', $row)->update(['c_Name' => '']);
        $before = DB::table('op_material')->get()->all();
        $issues = $this->issues();
        self::assertSame([['section' => 'project', 'code' => 'required', 'field' => 'Name', 'row' => $row]], $issues);
        self::assertEquals($before, DB::table('op_material')->get()->all());
    }

    public function test_an_application_without_a_beneficiary_is_not_ready(): void
    {
        DB::table('op_person_microproject')->update(['c_PersonType' => 'شريك']);
        self::assertSame([['section' => 'beneficiary', 'code' => 'required', 'field' => 'beneficiary', 'row' => null]], $this->issues());
    }

    /** @return list<array{section: string, code: string, field: string, row: ?int}> */
    private function issues(): array
    {
        $application = $this->tables->query('microproject')->where('p.id', $this->project)->first();

        return app(FullApplicationReadiness::class)->check($application);
    }

    private function person(array $changes): void
    {
        DB::table('op_person')->where('id', $this->person)->update($changes);
    }

    private function link(array $changes): void
    {
        DB::table('op_person_microproject')->where('c_PersonType', 'مستفيد')->update($changes);
    }

    private function row(string $kind, array $fields = [], array $references = []): int
    {
        return ApplicationFixture::row($kind, 1, $fields, $references);
    }
}
