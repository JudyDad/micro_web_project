<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Assessments\ChecklistService;
use App\Services\Finance\FinanceService;
use App\Services\Reports\Search\SearchModes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** P6.2: the eight remaining desktop search reports (applications, attachments, M&E visits, monitoring visits, check lists, special cases, educations, profession meetings). */
final class OtherSearchModesTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private int $project;

    private int $person;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        app()->setLocale('en');
        $this->account = ApplicationFixture::account();
        FinanceFixture::grant($this->account);
        FieldFixture::grant($this->account);
        foreach ([1, 2] as $city) {
            foreach (['projects.view', 'attachments.download', 'attachments.personal', 'attachments.legal', 'attachments.financial'] as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $this->account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
            }
        }
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->person = $this->intake['person'];
        DB::table('op_person')->where('id', $this->person)->update(['c_P_FirstName' => 'سامر', 'c_P_LastName' => 'خوري', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'ماري', 'c_P_ID' => 4242, 'c_P_Sex' => 'ذكر', 'c_P_NationalNumber' => '0123']);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        $this->actingAs($this->account);
    }

    private function lines(string $mode, array $filters = []): array
    {
        $search = SearchModes::make($mode);

        return array_map(fn ($l) => array_combine($search->headings(), $l), $search->page($this->account, ['mode' => $mode] + $filters)['rows']->getCollection()->all());
    }

    private function number(): int
    {
        return (int) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID');
    }

    public function test_all_22_desktop_modes_are_registered_and_list_without_failing_on_an_empty_database(): void
    {
        self::assertCount(22, SearchModes::keys());
        foreach (SearchModes::keys() as $key) {
            $search = SearchModes::make($key);
            self::assertNotEmpty($search->headings());
            foreach ($search->numeric() as $index) {
                self::assertArrayHasKey($index, $search->headings(), "$key numeric column $index exists");
            }
        }
    }

    public function test_applications_list_each_linked_person_with_project_status_loan_and_defaults(): void
    {
        $finance = app(FinanceService::class);
        $line = $this->lines('applications')[0];
        self::assertSame(['Aleppo', $this->number(), 'سامر خوري', 'جورج', 'ماري', 'Accepted', 'Preliminary', 0, 1], [$line['City'], (int) $line['Project number'], $line['Beneficiary'], $line['Father'], $line['Mother'], $line['Project status'], $line['Application type'], $line['Loan amount'], $line['Status count'] - 1]);
        self::assertSame('1900', $line['Date of birth'], 'a missing birth date shows as the desktop does');
        self::assertSame('Yes', $line['Project replaced']);
        self::assertSame('N/A', $line['Service during the war']);

        FinanceFixture::withDonor($this->account, $this->project, FinanceFixture::catalog(1)['donors']['a']);
        $finance->saveLoan($this->account, $this->project, FinanceFixture::loanInput(['date' => '2026-11-20']), $finance->show($this->account, $this->project)['version']);
        DB::table('op_microproject')->where('id', $this->project)->update(['c_MP_DateOfRequest' => '2026-10-19']);
        $withLoan = $this->lines('applications')[0];
        self::assertSame(['Financed', '2026-11-20', 1], [$withLoan['Project status'], $withLoan['Loan date'], $withLoan['Waiting months']]);
        self::assertSame('Synthetic donor A', $withLoan['Donor']);

        // A second linked person doubles the project's lines.
        $partner = ApplicationFixture::row('person', 1, ['c_P_ID' => 4243, 'c_P_FirstName' => 'ريم', 'c_P_LastName' => 'خوري']);
        ApplicationFixture::row('person_microproject', 1, ['c_PersonType' => 'شريك'], ['Person_ID' => $partner, 'MicroProject_ID' => $this->project]);
        $roles = array_column($this->lines('applications'), 'Applicant role');
        self::assertCount(2, $roles);
        self::assertContains('شريك', $roles);
        self::assertSame(['سامر خوري', 'ريم خوري'], array_values(array_unique(array_column($this->lines('applications'), 'Beneficiary'))));
        self::assertCount(0, $this->lines('applications', ['state' => 'Rejected']));
    }

    public function test_educations_prefer_the_members_own_education_and_fall_back_to_the_providers(): void
    {
        $level = ApplicationFixture::row('educationlevel', 1, ['c_ID' => 1, 'c_Name' => 'جامعي', 'c_Name_en' => 'University']);
        $type = ApplicationFixture::row('educationtype', 1, ['c_ID' => 1, 'c_Name' => 'إجازة', 'c_Name_en' => 'Degree']);
        $education = ApplicationFixture::row('education', 1, ['c_ID' => 1, 'c_Name' => 'هندسة', 'c_Name_en' => 'Engineering'], ['Type_ID' => $type]);
        $relation = ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $this->person, 'P_Provider_ID' => $this->person, 'Relation_ID' => $relation]);
        ApplicationFixture::row('person_education', 1, [], ['Person_ID' => $this->person, 'Level_ID' => $level, 'Education_ID' => $education]);

        $lines = $this->lines('educations');
        self::assertCount(1, $lines);
        self::assertSame(['سامر خوري', 'مستفيد', 'جامعي', 'إجازة', 'هندسة'], [$lines[0]['Name'], $lines[0]['Relation'], $lines[0]['Education level'], $lines[0]['Degree'], $lines[0]['Specialization']]);

        // A child has no education rows of their own but a level on the family row.
        $child = ApplicationFixture::row('person', 1, ['c_P_ID' => 5, 'c_P_FirstName' => 'ليلى', 'c_P_LastName' => 'خوري']);
        $school = ApplicationFixture::row('educationlevel', 1, ['c_ID' => 2, 'c_Name' => 'ابتدائي', 'c_Name_en' => 'Primary']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $child, 'P_Provider_ID' => $this->person, 'Relation_ID' => $relation, 'EducationLevel_ID' => $school]);
        $byName = array_column($this->lines('educations'), null, 'Name');
        self::assertSame(['ابتدائي', ''], [$byName['ليلى خوري']['Education level'], $byName['ليلى خوري']['Degree']]);
    }

    public function test_special_cases_and_profession_meetings(): void
    {
        $type = ApplicationFixture::row('specialcasetype', 1, ['c_ID' => 1, 'c_Name' => 'زيارة', 'c_Name_en' => 'Visit']);
        ApplicationFixture::row('specialcase', 1, ['c_Date' => '2026-10-02', 'c_Reason' => 'سبب', 'c_Description' => 'وصف', 'c_Action' => 'إجراء'], ['MicroProject_ID' => $this->project, 'Type_ID' => $type]);
        $cases = $this->lines('special-cases');
        self::assertCount(1, $cases);
        self::assertSame(['2026-10-02', 'زيارة', 'سبب', 'وصف', 'إجراء', 'سامر خوري'], [$cases[0]['Review date'], $cases[0]['Review type'], $cases[0]['Reason'], $cases[0]['Description'], $cases[0]['Action'], $cases[0]['Beneficiary']]);

        $career = ApplicationFixture::row('career', 1, ['c_ID' => 1, 'c_Name' => 'نجار', 'c_Name_en' => 'Carpenter']);
        $category = ApplicationFixture::row('category', 1, ['c_ID' => 1, 'c_Name' => 'حرف']);
        $sub = ApplicationFixture::row('subcategory', 1, ['c_ID' => 1, 'c_Name' => 'خشب'], ['Category_ID' => $category]);
        ApplicationFixture::row('profession_meeting', 1, ['c_ID' => 5, 'c_MeetingDate' => '2026-09-10', 'c_Place' => 'قاعة', 'c_InvitedCount' => 20, 'c_AttendanceCount' => 15, 'c_AbsenceCount' => 5], ['Career_ID' => $career, 'SubCategory_ID' => $sub]);
        ApplicationFixture::row('profession_meeting', 1, ['c_ID' => 6, 'c_MeetingDate' => '2026-10-10', 'c_Place' => 'ساحة'], ['Career_ID' => $career, 'SubCategory_ID' => $sub]);
        $meetings = $this->lines('meetings');
        self::assertSame([6, 5], array_map('intval', array_column($meetings, 'Meeting number')), 'newest first');
        self::assertSame(['Carpenter', 'نجار', 'حرف', 'خشب', 'قاعة', 20, 15, 5], [$meetings[1]['Career'], $meetings[1]['Career (Arabic)'], $meetings[1]['Category'], $meetings[1]['Sub category'], $meetings[1]['Place'], $meetings[1]['Invited'], $meetings[1]['Attended'], $meetings[1]['Absent']]);
        self::assertSame([0, 0, 0], [$meetings[0]['Invited'], $meetings[0]['Attended'], $meetings[0]['Absent']], 'missing counts are 0, as in the desktop');
        self::assertCount(1, $this->lines('meetings', ['meeting_from' => '2026-10-01']));
        self::assertCount(1, $this->lines('meetings', ['meeting_to' => '2026-09-10']));
        self::assertCount(0, $this->lines('meetings', ['meeting_from' => '2026-10-11']));
    }

    public function test_monitoring_visits_show_the_evaluation_range_and_the_first_and_last_visitor_marks(): void
    {
        ApplicationFixture::row('meresultranges', 1, ['c_ID' => 1, 'c_Min' => 0, 'c_Max' => 50, 'c_Evaluation' => 'ضعيف', 'c_Evaluation_en' => 'Weak']);
        ApplicationFixture::row('meresultranges', 1, ['c_ID' => 2, 'c_Min' => 51, 'c_Max' => 100, 'c_Evaluation' => 'جيد', 'c_Evaluation_en' => 'Good']);
        ApplicationFixture::row('person_microproject', 1, ['c_PersonType' => 'شريك'], ['Person_ID' => $this->person, 'MicroProject_ID' => $this->project]);
        $visit = ApplicationFixture::row('mevisit', 1, ['c_Kind' => 'v', 'c_Number' => '2', 'c_Date' => '2026-10-05', 'c_Profit' => 2500, 'c_Result' => 72], ['Person_ID' => $this->person]);
        foreach ([[1, 5], [2, 9]] as [$user, $mark]) {
            ApplicationFixture::row('mevisit_user', 1, ['c_User_ID' => $user, 'c_Mark' => $mark], ['Visit_ID' => $visit]);
        }
        $lines = $this->lines('me-visits');
        self::assertCount(2, $lines, 'once for each project the person is linked to');
        self::assertSame(['Vehicles', '2026-10-05', 2, 'جيد/Good', 5, 9], [$lines[0]['Visit type'], $lines[0]['Visit date'], (int) $lines[0]['Visit number'], $lines[0]['Evaluation'], (int) $lines[0]['Visitor 1 mark'], (int) $lines[0]['Visitor 2 mark']]);
        DB::table('op_mevisit')->where('id', $visit)->update(['c_Kind' => 'o', 'c_Result' => 10]);
        $other = $this->lines('me-visits')[0];
        self::assertSame(['Other projects', 'ضعيف/Weak'], [$other['Visit type'], $other['Evaluation']]);
    }

    public function test_visit_dates_show_opening_the_five_monitoring_visits_and_closing_per_project(): void
    {
        ApplicationFixture::row('visit', 1, ['c_Kind' => 'O-op', 'c_Date' => '2026-10-01'], ['MicroProject_ID' => $this->project]);
        ApplicationFixture::row('visit', 1, ['c_Kind' => 'O-cl', 'c_Date' => '2027-04-01'], ['MicroProject_ID' => $this->project]);
        foreach ([1 => '2026-12-01', 3 => '2027-02-01'] as $number => $date) {
            ApplicationFixture::row('mevisit', 1, ['c_Kind' => 'o', 'c_Number' => (string) $number, 'c_Date' => $date], ['Person_ID' => $this->person]);
        }
        $lines = $this->lines('visit-dates');
        self::assertCount(1, $lines);
        self::assertSame(['2026-10-01', '2026-12-01', null, '2027-02-01', null, null, '2027-04-01'], [$lines[0]['Opening visit'], $lines[0]['M1'], $lines[0]['M2'], $lines[0]['M3'], $lines[0]['M4'], $lines[0]['M5'], $lines[0]['Closing visit']]);
        self::assertStringContainsString('سامر خوري', $lines[0]['Beneficiaries']);
    }

    public function test_check_lists_show_flags_dates_visitors_and_member_counts(): void
    {
        $members = ApplicationFixture::checklistCatalog(1);
        $checklists = app(ChecklistService::class);
        $checklists->save($this->account, $this->project, $this->person, null, [
            'c_HasHomeVisit' => '1', 'c_HVisit_Date' => '2026-10-02', 'c_HVisit_Time' => '10:30', 'c_NeedProTest' => '0', 'c_TeamDate' => '2026-10-20', 'c_CommityDate' => '2026-10-25',
            'c_Notes' => "ملاحظات\nعامة", 'c_RejectMsg' => '0',
        ], ['HVisit_Visitor1_ID' => $members['t']], [
            ['location' => 'k', 'member_id' => $members['k'], 'name_notes' => 'اسم', 'role_notes' => 'دور', 'notes' => 'جيد'],
            ['location' => 't', 'member_id' => $members['t'], 'opinion' => '1'],
            ['location' => 'c', 'member_id' => $members['c'], 'opinion' => '0', 'notes' => 'تحفظ'],
        ], $checklists->show($this->account, $this->project, $this->person)['version']);

        $lines = $this->lines('checklists');
        self::assertCount(1, $lines);
        $line = $lines[0];
        self::assertSame(['Done', '2026-10-02', '10:30', 'Not needed', 'Not sent', '2026-10-20', '2026-10-25'], [$line['Home visit'], $line['Home visit date'], $line['Home visit time'], $line['Needs professional test'], $line['Rejection message'], $line['Team meeting date'], $line['Committee meeting date']]);
        self::assertSame([1, 1, 1], [(int) $line['Key people'], (int) $line['Team members'], (int) $line['Committee members']]);
        self::assertStringContainsString('Against - تحفظ', $line['Committee opinion']);
        self::assertStringContainsString('For', $line['Team opinion']);
        self::assertNotSame('', $line['Home visitor 1']);
        self::assertSame("ملاحظات\nعامة", $line['General notes']);
    }

    public function test_attachments_list_what_exists_by_type_without_paths_and_need_the_download_grant(): void
    {
        $type = ApplicationFixture::row('imagetype', 1, ['c_ID' => 1, 'c_Name' => 'هوية', 'c_Name_en' => 'ID card', 'c_Category' => 'ثبوتيات شخصية - Personal']);
        ApplicationFixture::row('image', 1, ['c_image_NewPath' => 'D:\\secret\\id.jpg'], ['MicroProject_ID' => $this->project, 'ImageType_ID' => $type]);
        $lines = $this->lines('attachments');
        self::assertCount(1, $lines);
        self::assertSame(['هوية', 'ID card', 'ثبوتيات شخصية - Personal'], [$lines[0]['Image type'], $lines[0]['Image type (English)'], $lines[0]['Category']]);
        self::assertStringNotContainsString('secret', $this->get('/en/search-reports?mode=attachments')->assertOk()->getContent());
        self::assertStringNotContainsString('secret', $this->get('/en/search-reports/export?mode=attachments&format=csv')->streamedContent());

        DB::table('account_city_capabilities')->where('capability', 'attachments.download')->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=attachments')->assertForbidden();
    }

    public function test_every_mode_needs_its_own_grants(): void
    {
        $needs = ['applications' => 'people.view', 'educations' => 'people.view', 'checklists' => 'assessments.view', 'special-cases' => 'special_cases.view', 'meetings' => 'profession_meetings.view', 'me-visits' => 'me_visits.view', 'visit-dates' => 'visits.view'];
        foreach ($needs as $mode => $capability) {
            $this->get('/en/search-reports/export?mode='.$mode.'&format=csv')->assertOk();
            DB::table('account_city_capabilities')->where('capability', $capability)->update(['revoked_at' => now()]);
            $this->get('/en/search-reports/export?mode='.$mode)->assertForbidden();
            DB::table('account_city_capabilities')->update(['revoked_at' => null]);
        }
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=meetings')->assertOk();
        self::assertSame([], $this->lines('applications', ['cities' => [1]]));
    }

    public function test_pages_render_for_the_new_modes_in_both_languages_with_the_meeting_filters(): void
    {
        $this->get('/en/search-reports?mode=meetings')->assertOk()->assertSee('Profession meetings')->assertSee('Meeting from')->assertSee('name="meeting_from"', false);
        $this->get('/ar/search-reports?mode=checklists')->assertOk()->assertSee('قوائم التقييم')->assertSee('dir="rtl"', false);
        foreach (['applications', 'attachments', 'visit-dates', 'me-visits', 'educations', 'special-cases'] as $mode) {
            $this->get('/en/search-reports?mode='.$mode)->assertOk();
        }
        $this->get('/en/search-reports?mode=meetings&meeting_from=2026-02-01&meeting_to=2026-01-01')->assertSessionHasErrors('meeting_to');
        $this->get('/en/search-reports?mode=meetings&meeting_to=2027-01-01')->assertOk()->assertSessionHasNoErrors();
        $this->get('/en/search-reports?mode=meetings&meeting_from=2020-01-01')->assertOk()->assertSessionHasNoErrors();
    }
}
