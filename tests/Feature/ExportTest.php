<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Assessments\ChecklistService;
use App\Services\Finance\FinanceService;
use App\Services\Reports\CsvExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** P6.2: CSV exports return every matching row inside the caller's own authorization, with Arabic text and no formula injection. */
final class ExportTest extends TestCase
{
    private WebAccount $account;

    private array $intake = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        FinanceFixture::grant($this->account);
        foreach ([1, 2] as $city) {
            $this->intake[$city] = ApplicationFixture::intake($city);
        }
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->actingAs($this->account);
    }

    public function test_application_export_returns_every_matching_row_not_one_page_and_matches_the_list_total(): void
    {
        foreach (range(1, 23) as $n) {
            $this->application(1, 'مشروع '.$n);
        }
        $this->application(2, 'Other city');
        $list = app(ApplicationService::class)->search($this->account, [], '', []);
        self::assertSame(24, $list->total());
        self::assertGreaterThan(count($list->items()), $list->total(), 'the list pages, the export must not');

        [$headers, $rows] = $this->csv('/en/applications/export');
        self::assertSame(['City', 'Project number', 'Name', 'Stage', 'Request date', 'Lifecycle status'], $rows[0]);
        self::assertCount(24, array_slice($rows, 1));
        self::assertSame('text/csv; charset=UTF-8', $headers->get('content-type'));
        self::assertStringContainsString('attachment; filename="applications-', $headers->get('content-disposition'));
        self::assertContains('مشروع 7', array_column($rows, 2));
        self::assertSame('On Hold', $rows[1][5]);

        [, $filtered] = $this->csv('/en/applications/export?cities[]=2');
        self::assertCount(1, array_slice($filtered, 1));
        [, $searched] = $this->csv('/en/applications/export?search='.urlencode('مشروع 2'));
        self::assertCount(DB::table('op_microproject')->where('c_MP_Name', 'like', '%مشروع 2%')->count(), array_slice($searched, 1));
    }

    public function test_same_project_number_in_two_cities_stays_distinguishable(): void
    {
        $a = $this->application(1, 'One');
        $b = $this->application(2, 'Two');
        DB::table('op_microproject')->whereIn('id', [$a, $b])->update(['c_MP_ID' => 1042]);
        [, $rows] = $this->csv('/en/applications/export');
        $numbers = array_map(fn ($r) => [$r[0], $r[1]], array_slice($rows, 1));
        self::assertCount(2, array_unique(array_map('json_encode', $numbers)));
        self::assertSame(['1042', '1042'], array_column($numbers, 1));
        self::assertCount(2, array_unique(array_column($numbers, 0)));
    }

    public function test_export_is_refused_for_other_cities_without_the_grant_and_for_bad_filters(): void
    {
        $this->application(1, 'Mine');
        DB::table('account_city_capabilities')->where('city_id', 2)->update(['revoked_at' => now()]);
        $this->get('/en/applications/export?cities[]=2')->assertForbidden();
        $this->get('/en/applications/export?date_from=2026-02-01&date_to=2026-01-01')->assertSessionHasErrors('date_to');
        [, $rows] = $this->csv('/en/applications/export');
        self::assertCount(1, array_slice($rows, 1));
        DB::table('account_city_capabilities')->where('capability', 'finance.view')->update(['revoked_at' => now()]);
        $this->get('/en/payments/export')->assertForbidden();
        $this->get('/en/applications/export')->assertOk();
        DB::table('account_city_capabilities')->where('capability', 'applications.view')->update(['revoked_at' => now()]);
        $this->get('/en/applications/export')->assertForbidden();
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'access.denied')->count());
    }

    public function test_every_export_is_audited_and_formula_cells_are_defused(): void
    {
        $id = $this->application(1, '=HYPERLINK("http://x","y")');
        DB::table('op_microproject')->where('id', $id)->update(['c_MP_Name' => '=HYPERLINK("http://x","y")']);
        $before = DB::table('account_audit_events')->where('event_type', 'reports.exported')->count();
        [, $rows] = $this->csv('/en/applications/export');
        self::assertSame("'=HYPERLINK(\"http://x\",\"y\")", $rows[1][2]);
        self::assertSame($before + 1, DB::table('account_audit_events')->where('event_type', 'reports.exported')->count());
        $cell = new CsvExport;
        self::assertSame("'+1", $cell->cell('+1'));
        self::assertSame("'@x", $cell->cell('@x'));
        self::assertSame('-5.25', $cell->cell('-5.25'));
        self::assertSame('', $cell->cell(null));
        self::assertSame('1042', $cell->cell(1042));
    }

    public function test_payment_export_lists_every_instalment_with_city_project_number_and_currency(): void
    {
        $catalog = FinanceFixture::catalog(1);
        $project = FinanceFixture::acceptedProject($this->account, $this->intake[1]);
        FinanceFixture::withDonor($this->account, $project, $catalog['donors']['a']);
        $finance = app(FinanceService::class);
        $loan = $finance->saveLoan($this->account, $project, FinanceFixture::loanInput(), $finance->show($this->account, $project)['version']);
        $rows = array_map(fn ($n) => ['amount' => '100', 'due_date' => '2026-1'.$n.'-01', 'paid' => '0', 'received_date' => '', 'note' => ''], [0, 1, 2]);
        $finance->savePayments($this->account, $project, $loan, $rows, $finance->show($this->account, $project)['version']);

        [, $csv] = $this->csv('/en/payments/export');
        self::assertSame(['City', 'Project number', 'Beneficiary', 'Partner', 'Group', 'Currency', 'Amount', 'Due date', 'Paid', 'Received date'], $csv[0]);
        self::assertCount(3, array_slice($csv, 1));
        self::assertSame(['USD', '100', '2026-10-01', 'Not paid'], [$csv[1][5], $csv[1][6], $csv[1][7], $csv[1][8]]);
        self::assertSame('', $csv[1][9]);
        [, $none] = $this->csv('/en/payments/export?state=paid');
        self::assertCount(0, array_slice($none, 1));
        $paidRows = array_map(fn ($n) => ['id' => DB::table('op_payment')->orderBy('id')->skip($n)->value('id'), 'amount' => '100', 'due_date' => '2026-1'.$n.'-01', 'paid' => $n === 0 ? '1' : '0', 'received_date' => $n === 0 ? '2026-10-03' : '', 'note' => ''], [0, 1, 2]);
        $finance->savePayments($this->account, $project, $loan, $paidRows, $finance->show($this->account, $project)['version']);
        [, $paid] = $this->csv('/en/payments/export?state=paid');
        self::assertSame(['Paid', '2026-10-03'], [$paid[1][8], $paid[1][9]]);
        [, $year] = $this->csv('/en/payments/export?year=2026&months[]=11');
        self::assertCount(1, array_slice($year, 1));
        $this->get('/en/payments/export?year=2017')->assertSessionHasErrors('year');
    }

    public function test_export_links_carry_the_current_filters_on_both_pages(): void
    {
        $this->application(1, 'Linked');
        $this->get('/en/applications?cities[]=1&state=Accepted')->assertOk()->assertSee('/en/applications/export?cities%5B0%5D=1&amp;state=Accepted', false)->assertSee('Export all matching rows (CSV)');
        $this->get('/ar/payments?year=2026')->assertOk()->assertSee('/ar/payments/export?year=2026', false)->assertSee('تصدير كل الصفوف المطابقة');
    }

    public function test_checklist_print_shows_the_saved_checklist_in_both_languages_and_needs_the_manage_grant(): void
    {
        $project = $this->application(1, 'مشروع للطباعة');
        $person = $this->intake[1]['person'];
        $members = ApplicationFixture::checklistCatalog(1);
        $checklists = app(ChecklistService::class);
        $checklists->save($this->account, $project, $person, null, ['c_Notes' => "ملاحظات\nبسطرين", 'c_TeamDate' => '2026-10-20', 'c_HasHomeVisit' => '1', 'c_HVisit_Time' => '10:30'], ['HVisit_Visitor1_ID' => $members['t']], [
            ['location' => 't', 'member_id' => $members['t'], 'opinion' => '0', 'notes' => 'ضد'],
            ['location' => 'k', 'member_id' => $members['k'], 'name_notes' => 'اسم', 'role_notes' => 'دور'],
        ], $checklists->show($this->account, $project, $person)['version']);

        $url = '/en/applications/'.$project.'/checklist/'.$person.'/print';
        $this->get($url)->assertOk()->assertSee('Assessment checklist')->assertSee('2026-10-20')->assertSee('10:30')->assertSee('ضد')->assertSee('Against')->assertSee('اسم')->assertSee('window.print()', false)
            ->assertDontSee('Save', false);
        $this->get('/ar/applications/'.$project.'/checklist/'.$person.'/print')->assertOk()->assertSee('dir="rtl"', false)->assertSee('طباعة')->assertSee("ملاحظات\nبسطرين");
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'assessments.printed')->count());
        $this->get('/en/applications/'.$project.'/checklist/'.$person)->assertOk()->assertSee($url, false);

        DB::table('account_city_capabilities')->where('capability', 'assessments.manage')->update(['revoked_at' => now()]);
        $this->get($url)->assertForbidden();
        $this->get('/en/applications/'.$project.'/checklist/'.$person)->assertOk()->assertDontSee($url, false);
        DB::table('account_city_capabilities')->update(['revoked_at' => null]);
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->get($url)->assertForbidden();
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'assessments.printed')->count());
    }

    public function test_checklist_print_without_a_saved_checklist_says_so_instead_of_inventing_one(): void
    {
        $project = $this->application(1, 'بدون قائمة');
        $this->get('/en/applications/'.$project.'/checklist/'.$this->intake[1]['person'].'/print')->assertOk()->assertSee('No checklist has been saved');
    }

    private function application(int $city, string $name): int
    {
        return app(ApplicationService::class)->create($this->account, $city, ['c_MP_Name' => $name, 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[$city]['person'], $this->intake[$city]['references']);
    }

    /** @return array{0: HeaderBag, 1: list<list<string>>} */
    private function csv(string $url): array
    {
        $response = $this->get($url)->assertOk();
        $body = $response->streamedContent();
        self::assertStringStartsWith("\xEF\xBB\xBF", $body);
        $lines = array_map('str_getcsv', array_filter(explode("\r\n", substr($body, 3)), fn ($l) => $l !== ''));

        return [$response->baseResponse->headers, array_values($lines)];
    }
}
