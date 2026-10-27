<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use App\Services\Reports\CsvExport;
use App\Services\Reports\Search\SearchModes;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** P6.2: the desktop "Loans and Payments" and "Family Members" search reports, on screen and as Excel/CSV, with the desktop formulas on exact decimals. */
final class SearchReportsTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $catalog;

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
            DB::table('account_city_capabilities')->insert(['account_id' => $this->account->id, 'city_id' => $city, 'capability' => 'people.view', 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        }
        config(['session.block_store' => 'array']);
        CarbonImmutable::setTestNow('2026-10-26 12:00:00');
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FinanceFixture::catalog(1);
        $this->actingAs($this->account);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    /** A financed project with a loan and instalments; returns the project id. */
    private function financed(array $loan = [], array $instalments = [], int $donor = 0): int
    {
        $project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FinanceFixture::withDonor($this->account, $project, $this->catalog['donors'][['a', 'b'][$donor]]);
        $finance = app(FinanceService::class);
        $loanId = $finance->saveLoan($this->account, $project, FinanceFixture::loanInput($loan), $finance->show($this->account, $project)['version']);
        if ($instalments !== []) {
            $finance->savePayments($this->account, $project, $loanId, $instalments, $finance->show($this->account, $project)['version']);
        }

        return $project;
    }

    private function instalment(string $amount, string $due, bool $paid = false, string $received = ''): array
    {
        return ['amount' => $amount, 'due_date' => $due, 'paid' => $paid ? '1' : '0', 'received_date' => $received, 'note' => ''];
    }

    public function test_loan_report_applies_the_desktop_formulas_per_loan_and_currency(): void
    {
        // USD loan 1000 at 15000, to return 1200 in instalments of 120; three paid (360), seven to go, the latest due 2026-07-01 (months late).
        $rows = [];
        foreach (range(1, 10) as $n) {
            $rows[] = $this->instalment('120', sprintf('2026-%02d-01', min($n, 12)), $n <= 3, $n <= 3 ? sprintf('2026-%02d-05', $n) : '');
        }
        $project = $this->financed(['currency' => 'USD', 'exchange_rate' => '15000', 'amount' => '1000', 'returned' => '1200', 'payment' => '120', 'date' => '2026-01-10', 'receipt' => 'R-9', 'note' => 'ملاحظة'], $rows);
        DB::table('op_loan')->update(['c_Loan_Rate' => 20]);

        $page = SearchModes::make('loans')->page($this->account, ['mode' => 'loans']);
        self::assertSame(1, $page['total']);
        $line = array_combine(SearchModes::make('loans')->headings(), $page['rows']->getCollection()->first());
        self::assertSame('Aleppo', $line['City']);
        self::assertSame((int) DB::table('op_microproject')->where('id', $project)->value('c_MP_ID'), (int) $line['Project number']);
        self::assertSame('2026-01-10', $line['Loan date']);
        self::assertSame(['USD', '15000'], [$line['Currency'], (string) (float) $line['Exchange rate']]);
        self::assertSame('15000000.00', $line['Loan (SP)']);
        self::assertSame('1000', $line['Loan (USD)']);
        self::assertSame('120.00', $line['Return rate %']);
        self::assertSame(['1200', '120', '360', '840'], [(string) $line['Amount to return'], (string) $line['Instalment'], (string) $line['Paid amount'], (string) $line['Remaining amount']]);
        self::assertSame(['10.00', 3, 7], [$line['Total instalments'], $line['Paid instalments'], (int) round((float) $line['Unpaid instalments'])]);
        // (1000 × 20 ÷ 100 − 360) is negative, so the desktop's remaining-instalments figure clamps to 0 (it uses the stored rate, not the returned amount).
        self::assertSame('0.00', $line['Remaining instalments']);
        self::assertSame(['2026-01-01', '2026-03-05'], [$line['First instalment date'], $line['Last paid date']]);
        self::assertSame('2026-10-01', $line['Due instalment date'], 'the latest unpaid due date, shown while the project is Financed');
        self::assertSame(['R-9', 'ملاحظة'], [$line['Receipt number'], $line['Note']]);
        self::assertSame('Synthetic donor A', $line['Donor']);
        // Latest unpaid due date is 2026-10-01 (10th instalment); 2026-10-01 → 2026-11-26 is one full month.
        self::assertSame(1, $line['Months late']);
        self::assertStringContainsString('Synthetic', $line['Beneficiaries']);
    }

    public function test_a_finished_project_shows_no_due_date_and_no_delay(): void
    {
        $project = $this->financed(['currency' => 'SP', 'amount' => '1000', 'exchange_rate' => '1', 'returned' => '300', 'payment' => '100'], [$this->instalment('100', '2026-01-01'), $this->instalment('100', '2026-02-01')]);
        $lifecycle = app(LifecycleService::class);
        $lifecycle->transition($this->account, $project, $this->intake['states']['Finished'], null, null, $lifecycle->panel($this->account, $project)['version']);
        $line = array_combine(SearchModes::make('loans')->headings(), SearchModes::make('loans')->page($this->account, ['mode' => 'loans'])['rows']->getCollection()->first());
        self::assertSame(['', ''], [(string) $line['Due instalment date'], (string) $line['Months late']]);
        self::assertSame(2, (int) $line['Unpaid instalments']);
    }

    public function test_loan_report_figures_for_an_sp_loan_a_repaid_loan_and_no_payments(): void
    {
        $this->financed(['currency' => 'SP', 'exchange_rate' => '15000', 'amount' => '30000000', 'returned' => '9000000', 'payment' => '1000000', 'date' => '2026-03-01'],
            [$this->instalment('1000000', '2026-04-01', true, '2026-04-02'), $this->instalment('8000000', '2026-05-01', true, '2026-05-02')]);
        $this->financed(['currency' => 'SP', 'amount' => '500', 'exchange_rate' => '0', 'returned' => '150', 'payment' => '50', 'date' => '2026-02-01']);
        $lines = array_map(fn ($l) => array_combine(SearchModes::make('loans')->headings(), $l), SearchModes::make('loans')->page($this->account, ['mode' => 'loans'])['rows']->getCollection()->all());
        usort($lines, fn ($a, $b) => (int) $a['Project number'] <=> (int) $b['Project number']);

        [$repaid, $unpaid] = $lines;
        self::assertSame(['30000000', '2000.00', '30.00'], [(string) $repaid['Loan (SP)'], $repaid['Loan (USD)'], $repaid['Return rate %']]);
        self::assertSame(['9000000', '0', 0], [(string) $repaid['Paid amount'], (string) $repaid['Remaining amount'], $repaid['Months late']], 'fully repaid: no remaining amount and no delay');
        self::assertSame(['500', ''], [(string) $unpaid['Loan (SP)'], (string) $unpaid['Loan (USD)']], 'a zero exchange rate gives no USD figure');
        self::assertSame(['0', '', '', '3.00'], [(string) $unpaid['Paid amount'], (string) $unpaid['Remaining amount'], (string) $unpaid['Remaining instalments'], $unpaid['Total instalments']], 'no paid instalment: the desktop leaves both remaining figures blank');
    }

    public function test_loan_report_filters_and_all_pages_export(): void
    {
        $a = $this->financed(['date' => '2026-01-10'], [], 0);
        $this->financed(['date' => '2026-06-10'], [], 1);
        $numbers = fn (string $f) => array_column($this->csv('/en/search-reports/export?mode=loans'.$f)[1], 1);
        $csv = $this->csv('/en/search-reports/export?mode=loans');
        self::assertCount(3, $csv[1], 'header plus two loans');
        self::assertSame('Project number', $csv[1][0][1]);

        self::assertCount(2, array_slice($csv[1], 1));
        self::assertCount(1, array_slice($numbers('&donor='.urlencode('Synthetic donor A')), 1));
        self::assertCount(1, array_slice($numbers('&loan_from=2026-05-01'), 1));
        self::assertCount(1, array_slice($numbers('&loan_to=2026-05-01'), 1));
        self::assertCount(2, array_slice($numbers('&state=Financed'), 1));
        self::assertCount(0, array_slice($numbers('&state=Rejected'), 1));
        self::assertCount(0, array_slice($numbers('&donor=nobody'), 1));
        self::assertCount(1, array_slice($numbers('&q='.DB::table('op_microproject')->where('id', $a)->value('c_MP_ID')), 1));
        self::assertCount(2, array_slice($numbers('&request_from=2026-10-01&request_to=2026-10-31'), 1));
        self::assertCount(0, array_slice($numbers('&request_from=2027-01-01'), 1));
        self::assertSame('Aleppo', $csv[1][1][0]);

        // More loans than a page holds: the page paginates, the export does not.
        foreach (range(1, 52) as $n) {
            $this->financed();
        }
        $page = SearchModes::make('loans')->page($this->account, ['mode' => 'loans']);
        self::assertSame(54, $page['total']);
        self::assertCount(50, $page['rows']->items());
        self::assertCount(54, array_slice($this->csv('/en/search-reports/export?mode=loans')[1], 1));
    }

    public function test_family_report_lists_each_person_family_relation_and_project_link(): void
    {
        app()->setLocale('en');
        $relation = ApplicationFixture::row('relation', 1, ['c_ID' => 3, 'c_Name' => 'ابن', 'c_Name_en' => 'Son']);
        $state = ApplicationFixture::row('personstate', 1, ['c_ID' => 2, 'c_Name' => 'مقيم', 'c_Name_en' => 'Resident', 'c_Type' => 'a']);
        $family = ApplicationFixture::row('family', 1, ['c_F_ID' => 9, 'c_F_Number' => 'F-7']);
        $provider = $this->intake['person'];
        $child = ApplicationFixture::row('person', 1, ['c_P_ID' => 77, 'c_P_FirstName' => 'سامر', 'c_P_LastName' => 'خوري', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'ماري', 'c_P_NationalNumber' => '0123', 'c_P_Sex' => 'ذكر', 'c_P_DOB' => '2010-04-05']);
        ApplicationFixture::row('person_family', 1, ['c_HasWork' => 1, 'c_Work_Name' => 'مدرسة', 'c_Family_ID' => 9, 'c_P_Provider_ID' => 1], ['Person_ID' => $child, 'Family_ID' => $family, 'Relation_ID' => $relation, 'P_Provider_ID' => $provider]);
        DB::table('op_person')->where('id', $child)->update([$this->personStateColumn() => $state]);
        $project = FinanceFixture::acceptedProject($this->account, $this->intake);
        ApplicationFixture::row('person_microproject', 1, ['c_PersonType' => 'شريك'], ['Person_ID' => $child, 'MicroProject_ID' => $project]);

        $mode = SearchModes::make('family');
        $lines = array_map(fn ($l) => array_combine($mode->headings(), $l), $mode->page($this->account, ['mode' => 'family', 'q' => 'سامر'])['rows']->getCollection()->all());
        self::assertCount(1, $lines);
        $line = $lines[0];
        self::assertSame(['F-7', 'سامر خوري', 'جورج', 'ماري', 'Son', '0123', 'ذكر', '2010-04-05', 'Resident', 'Yes', 'مدرسة', 9], [$line['Family book number'], $line['Person'], $line['Father'], $line['Mother'], $line['Relation'], $line['National number'], $line['Sex'], $line['Date of birth'], $line['Residence state'], $line['Works'], $line['Work'], $line['Family number']]);
        self::assertSame((int) DB::table('op_microproject')->where('id', $project)->value('c_MP_ID'), (int) $line['Project number']);

        // The beneficiary from the intake has no family link and no project: one line with blanks, like a left join.
        $all = array_map(fn ($l) => array_combine($mode->headings(), $l), $mode->page($this->account, ['mode' => 'family'])['rows']->getCollection()->all());
        self::assertCount(2, $all);
        $alone = collect($all)->firstWhere('Person', '!=', 'سامر خوري');
        self::assertSame(['', ''], [(string) $alone['Family book number'], (string) $alone['Relation']]);
        self::assertCount(1, array_slice($this->csv('/ar/search-reports/export?mode=family&q='.urlencode('0123'))[1], 1));
        self::assertSame('ابن', array_combine($mode->headings(), $this->csv('/ar/search-reports/export?mode=family&q='.urlencode('0123'))[1][1])['Relation']);
    }

    public function test_a_lone_dash_placeholder_is_exported_unchanged_but_formula_lookalikes_are_defused(): void
    {
        $csv = new CsvExport;
        self::assertSame('-', $csv->cell('-'));
        self::assertSame('+', $csv->cell('+'));
        self::assertSame("'-1+2", $csv->cell('-1+2'));
        self::assertSame("'=SUM(A1)", $csv->cell('=SUM(A1)'));
        self::assertSame("'@cmd", $csv->cell('@cmd'));
        self::assertSame('-5.25', $csv->cell('-5.25'));
    }

    public function test_workbook_export_has_numbers_and_the_same_rows_as_the_csv(): void
    {
        $this->financed(['currency' => 'USD', 'exchange_rate' => '15000', 'amount' => '1000', 'returned' => '1200', 'payment' => '120'], [$this->instalment('120', '2026-11-01')]);
        $response = $this->get('/en/search-reports/export?mode=loans&format=xlsx')->assertOk();
        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->baseResponse->headers->get('content-type'));
        self::assertMatchesRegularExpression('/search-loans-\d{8}-\d{6}\.xlsx/', $response->baseResponse->headers->get('content-disposition'));
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());
        $reader = new Reader;
        $reader->open($path);
        $sheet = [];
        foreach ($reader->getSheetIterator() as $s) {
            foreach ($s->getRowIterator() as $row) {
                $sheet[] = $row->toArray();
            }
            break;
        }
        $reader->close();
        unlink($path);
        $csv = $this->csv('/en/search-reports/export?mode=loans')[1];
        self::assertCount(count($csv), $sheet);
        self::assertSame($csv[0], $sheet[0]);
        $head = array_flip($sheet[0]);
        self::assertSame(1200, $sheet[1][$head['Amount to return']]);
        self::assertIsInt($sheet[1][$head['Project number']]);
        self::assertSame('USD', $sheet[1][$head['Currency']]);
    }

    public function test_the_report_needs_its_grants_and_never_shows_other_cities_and_is_audited(): void
    {
        $this->financed();
        $before = DB::table('account_audit_events')->where('event_type', 'reports.exported')->count();
        $this->csv('/en/search-reports/export?mode=loans');
        self::assertSame($before + 1, DB::table('account_audit_events')->where('event_type', 'reports.exported')->count());
        $this->get('/en/search-reports?mode=loans')->assertOk();
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'reports.search')->count());

        $this->get('/en/search-reports/export?mode=nope')->assertSessionHasErrors('mode');
        $this->get('/en/search-reports/export?mode=loans&format=nope')->assertSessionHasErrors('format');
        $this->get('/en/search-reports/export?mode=loans&request_from=2026-02-01&request_to=2026-01-01')->assertSessionHasErrors('request_to');

        $other = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(2), 2);
        self::assertGreaterThan(0, $other);
        DB::table('account_city_capabilities')->where('city_id', 2)->update(['revoked_at' => now()]);
        self::assertCount(1, array_slice($this->csv('/en/search-reports/export?mode=loans&cities[]=2')[1], 1), 'an unpermitted requested city is ignored, as on the other pages');
        DB::table('account_city_capabilities')->where('capability', 'finance.view')->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=loans')->assertForbidden();
        $this->get('/en/search-reports?mode=loans')->assertForbidden();
        $this->csv('/en/search-reports/export?mode=family');
        DB::table('account_city_capabilities')->where('capability', 'people.view')->update(['revoked_at' => now()]);
        $this->get('/en/search-reports/export?mode=family')->assertForbidden();
    }

    public function test_the_page_renders_in_both_languages_with_filters_export_links_and_pagination(): void
    {
        foreach (range(1, 3) as $n) {
            $this->financed();
        }
        $this->get('/en/search-reports?mode=loans&state=Financed')->assertOk()->assertSee('Search reports')->assertSee('Loans and payments')->assertSee('Rows: 3.')
            ->assertSee('/en/search-reports/export?mode=loans&amp;state=Financed&amp;format=xlsx', false)->assertSee('Export all matching rows (Excel)')->assertSee('Loan (SP)')->assertSee('Synthetic donor A');
        $this->get('/ar/search-reports?mode=family')->assertOk()->assertSee('dir="rtl"', false)->assertSee('تقارير البحث')->assertSee('أفراد العائلة');
        $this->get('/en/statistics')->assertOk()->assertSee('/en/search-reports', false);
        $this->get('/en/search-reports?mode=loans&request_from=bad')->assertSessionHasErrors('request_from');
    }

    private function personStateColumn(): string
    {
        return app(OperationalTables::class)->relation('person', 'P_State_ID')['resolved_column'];
    }

    /** @return array{0: HeaderBag, 1: list<list<string>>} */
    private function csv(string $url): array
    {
        $response = $this->get($url.(str_contains($url, '?') ? '&' : '?').'format=csv')->assertOk();
        $body = $response->streamedContent();
        self::assertStringStartsWith("\xEF\xBB\xBF", $body);
        $lines = array_map('str_getcsv', array_filter(explode("\r\n", substr($body, 3)), fn ($l) => $l !== ''));

        return [$response->baseResponse->headers, array_values($lines)];
    }
}
