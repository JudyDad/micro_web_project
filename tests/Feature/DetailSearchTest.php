<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Operational\OperationalTables;
use App\Services\Reports\Search\DetailSearch;
use App\Services\Reports\Search\SearchModes;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** P6.2: the twelve desktop "detail table" search reports (assets, losses, materials, …) listed with their project. */
final class DetailSearchTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private int $project;

    private int $beneficiary;

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
        foreach ([1, 2] as $city) {
            DB::table('account_city_capabilities')->insert(['account_id' => $this->account->id, 'city_id' => $city, 'capability' => 'people.view', 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        }
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->beneficiary = $this->intake['person'];
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        DB::table('op_person')->where('id', $this->beneficiary)->update(['c_P_ID' => 4242]);
        $this->selfFamily($this->beneficiary);
        $this->actingAs($this->account);
    }

    /** The beneficiary's own family row (provider = self), as the desktop's joins expect; creating the project already linked the person to it. */
    private function selfFamily(int $person): void
    {
        $relation = ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $person, 'P_Provider_ID' => $person, 'Relation_ID' => $relation]);
    }

    private function lines(string $mode, array $filters = []): array
    {
        $search = SearchModes::make($mode);

        return array_map(fn ($l) => array_combine($search->headings(), $l), $search->page($this->account, ['mode' => $mode] + $filters)['rows']->getCollection()->all());
    }

    public function test_every_detail_mode_is_registered_and_lists_with_its_own_headings(): void
    {
        self::assertCount(12, DetailSearch::DEFINITIONS);
        foreach (array_keys(DetailSearch::DEFINITIONS) as $key) {
            self::assertContains($key, SearchModes::keys());
            self::assertSame([], $this->lines($key), "$key lists nothing on an empty database without failing");
            self::assertSame(['City', 'Project number'], array_slice(SearchModes::make($key)->headings(), 0, 2));
        }
    }

    public function test_assets_show_catalog_names_the_person_the_relation_and_the_providers_project(): void
    {
        $type = ApplicationFixture::row('assettype', 1, ['c_ID' => 1, 'c_Name' => 'ملك', 'c_Name_en' => 'Owned']);
        $kind = ApplicationFixture::row('asset', 1, ['c_ID' => 2, 'c_Name' => 'مركبة', 'c_Name_en' => 'Vehicle']);
        $benefit = ApplicationFixture::row('assetbenefits', 1, ['c_ID' => 3, 'c_Name' => 'لا استفادة', 'c_Name_en' => 'None']);
        ApplicationFixture::row('person_asset', 1, ['c_Year' => '2022', 'c_Description' => '- سيارة قديمة', 'c_Note' => "سطر\nثان"], ['Person_ID' => $this->beneficiary, 'Asset_Type_ID' => $type, 'Asset_ID' => $kind, 'Asset_Benefits_ID' => $benefit]);

        $lines = $this->lines('assets');
        self::assertCount(1, $lines);
        $line = $lines[0];
        self::assertSame('Aleppo', $line['City']);
        self::assertSame((int) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID'), (int) $line['Project number']);
        self::assertSame(['مستفيد', 'ملك', '2022', 'مركبة', '- سيارة قديمة', 'لا استفادة', "سطر\nثان"], [$line['Relation'], $line['Asset type'], $line['Year of purchase'], $line['Asset kind'], $line['Description'], $line['Benefit'], $line['Notes']]);
        self::assertStringContainsString('Synthetic', $line['Name']);
    }

    public function test_a_family_members_row_is_listed_under_the_providers_project_and_a_person_with_two_links_appears_twice(): void
    {
        $son = ApplicationFixture::row('person', 1, ['c_P_ID' => 90, 'c_P_FirstName' => 'سامر', 'c_P_LastName' => 'خوري']);
        $relation = ApplicationFixture::row('relation', 1, ['c_ID' => 3, 'c_Name' => 'ابن', 'c_Name_en' => 'Son']);
        $family = ApplicationFixture::row('family', 1, ['c_F_ID' => 1, 'c_F_Number' => 'F-1']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $son, 'Family_ID' => $family, 'P_Provider_ID' => $this->beneficiary, 'Relation_ID' => $relation]);
        $loss = ApplicationFixture::row('loss', 1, ['c_ID' => 1, 'c_Name' => 'منزل', 'c_Name_en' => 'Home']);
        ApplicationFixture::row('person_loss', 1, ['c_Year' => '2015', 'c_Note' => 'قصف'], ['Person_ID' => $son, 'Loss_ID' => $loss]);

        $lines = $this->lines('losses');
        self::assertCount(1, $lines);
        self::assertSame(['سامر خوري', 'ابن', 'منزل', '2015', 'قصف'], [$lines[0]['Name'], $lines[0]['Relation'], $lines[0]['Loss type'], $lines[0]['Year'], $lines[0]['Notes']]);

        // A second family link for the same person (another family book) repeats the row, as the desktop's left join does.
        $other = ApplicationFixture::row('family', 1, ['c_F_ID' => 2, 'c_F_Number' => 'F-2']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $son, 'Family_ID' => $other, 'P_Provider_ID' => $this->beneficiary, 'Relation_ID' => $relation]);
        self::assertCount(2, $this->lines('losses'));
    }

    public function test_current_works_and_work_experiences_split_on_the_work_status(): void
    {
        $work = ApplicationFixture::row('work', 1, ['c_W_ID' => 5, 'c_W_Name' => 'خياط']);
        $type = ApplicationFixture::row('worktype', 1, ['c_ID' => 1, 'c_Name' => 'حر', 'c_Name_en' => 'Free']);
        $fields = ['c_W_Description' => 'محل', 'c_W_BeginDate' => '2019-03-04 00:00:00', 'c_W_EndDate' => '2021-01-01', 'c_W_PlaceState' => 'ملك', 'c_W_Place' => 'الميدان', 'c_W_Contact' => 'أبو علي', 'c_W_Salary' => '500', 'c_W_CauseOfLoseDescription' => 'إغلاق'];
        ApplicationFixture::row('person_workexp', 1, $fields + ['c_W_Status' => 'Yes'], ['Person_ID' => $this->beneficiary, 'Work_ID' => $work, 'W_WorkType_ID' => $type]);
        ApplicationFixture::row('person_workexp', 1, $fields + ['c_W_Status' => 'NO'], ['Person_ID' => $this->beneficiary, 'Work_ID' => $work, 'W_WorkType_ID' => $type]);

        $current = $this->lines('current-works');
        $past = $this->lines('work-experiences');
        self::assertCount(1, $current);
        self::assertCount(1, $past);
        self::assertSame(['خياط', 'حر', '2019-03-04', '500'], [$current[0]['Work'], $current[0]['Work type'], $current[0]['Start date'], $current[0]['Profit / salary']]);
        self::assertSame(['2021-01-01', 'إغلاق'], [$past[0]['End date'], $past[0]['Cause of stopping']]);
        self::assertArrayNotHasKey('End date', $current[0]);
    }

    public function test_project_modes_list_the_projects_rows_with_its_people(): void
    {
        ApplicationFixture::row('material', 1, ['c_Name' => 'ماكينة', 'c_Comments' => 'جديدة', 'c_Amount' => '2', 'c_Price' => '150'], ['MicroProject_ID' => $this->project]);
        $source = ApplicationFixture::row('powersource', 1, ['c_ID' => 1, 'c_Name' => 'مولدة', 'c_Name_en' => 'Generator']);
        ApplicationFixture::row('microproject_powersource', 1, ['c_Note' => 'بنزين', 'c_Cost' => '40', 'c_Responsability' => 'المستفيد'], ['MicroProject_ID' => $this->project, 'PowerSource_ID' => $source]);

        $materials = $this->lines('materials');
        self::assertCount(1, $materials);
        self::assertSame(['ماكينة', 'جديدة', '2', '150'], [$materials[0]['Item'], $materials[0]['Type'], (string) $materials[0]['Quantity'], (string) $materials[0]['Unit price']]);
        self::assertStringContainsString('Synthetic', (string) $materials[0]['Beneficiary']);
        $power = $this->lines('project-power');
        self::assertSame(['مولدة', 'بنزين', 'المستفيد'], [$power[0]['Power source'], $power[0]['System description'], $power[0]['Responsible party']]);
    }

    public function test_an_unresolved_person_or_catalog_link_falls_back_to_the_legacy_number_in_the_same_city(): void
    {
        // As in Latakia's person_aidsource: the source declares no foreign keys, so the import leaves the links unresolved.
        $aid = ApplicationFixture::row('aidsource', 1, ['c_ID' => 6, 'c_Name' => 'اللاتين', 'c_Name_en' => 'Latin']);
        $row = ApplicationFixture::row('person_aidsource', 1, ['c_DateYear' => '2024', 'c_Amount' => '16', 'c_Notes' => 'ماكينات', 'c_AidSource_ID' => 6, 'c_Person_ID' => 4242], ['Person_ID' => $this->beneficiary, 'AidSource_ID' => $aid]);
        $tables = app(OperationalTables::class);
        foreach (['Person_ID', 'AidSource_ID'] as $key) {
            $relation = $tables->relation('person_aidsource', $key);
            DB::table('op_person_aidsource')->where('id', $row)->update([$relation['resolved_column'] => null, $relation['state_column'] => 'not_applicable']);
        }

        $lines = $this->lines('aid-sources');
        self::assertCount(1, $lines);
        self::assertSame(['اللاتين', '2024'], [$lines[0]['Funding body'], $lines[0]['Year of benefit']]);
        self::assertStringContainsString('Synthetic', $lines[0]['Name']);

        // The same legacy number in another city must not match.
        DB::table('op_person_aidsource')->where('id', $row)->update(['city_id' => 2, 'source_id' => 2]);
        self::assertSame([], $this->lines('aid-sources'));
    }

    public function test_projects_without_a_current_status_and_other_cities_are_left_out_and_filters_apply(): void
    {
        $type = ApplicationFixture::row('goal', 1, ['c_ID' => 1, 'c_Name' => 'شراء', 'c_Name_en' => 'Buy']);
        ApplicationFixture::row('person_goals', 1, [], ['Person_ID' => $this->beneficiary, 'Goal_ID' => $type]);
        self::assertCount(1, $this->lines('goals'));
        self::assertCount(1, $this->lines('goals', ['state' => 'Accepted']));
        self::assertCount(0, $this->lines('goals', ['state' => 'Rejected']));
        self::assertCount(0, $this->lines('goals', ['request_from' => '2027-01-01']));
        self::assertCount(1, $this->lines('goals', ['request_from' => '2026-10-19', 'request_to' => '2026-10-19']));
        self::assertCount(1, $this->lines('goals', ['q' => (string) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID')]));

        DB::table('op_microproject_state')->delete();
        self::assertSame([], $this->lines('goals'), 'the desktop lists only projects that have a current status');
    }

    public function test_the_page_and_exports_work_for_a_detail_mode_and_need_the_modes_grants(): void
    {
        $loss = ApplicationFixture::row('loss', 1, ['c_ID' => 1, 'c_Name' => 'منزل', 'c_Name_en' => 'Home']);
        ApplicationFixture::row('person_loss', 1, ['c_Year' => '2015'], ['Person_ID' => $this->beneficiary, 'Loss_ID' => $loss]);

        $this->get('/en/search-reports?mode=losses')->assertOk()->assertSee('Losses')->assertSee('Rows: 1.')->assertSee('منزل')->assertSee('/en/search-reports/export?mode=losses&amp;format=xlsx', false);
        $csv = $this->get('/en/search-reports/export?mode=losses&format=csv')->assertOk()->streamedContent();
        self::assertStringContainsString('Loss type', $csv);
        self::assertStringContainsString('منزل', $csv);
        $this->get('/ar/search-reports?mode=materials')->assertOk()->assertSee('المواد المطلوبة للمشروع');
        $this->get('/en/search-reports?mode=nope')->assertSessionHasErrors('mode');

        DB::table('account_city_capabilities')->where('capability', 'people.view')->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=losses')->assertForbidden();
        $this->get('/en/search-reports/export?mode=materials')->assertOk();
        DB::table('account_city_capabilities')->where('capability', 'applications.view')->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=materials')->assertForbidden();
    }
}
