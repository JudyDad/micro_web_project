<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Finance\FinanceService;
use App\Services\Operational\OperationalTables;
use App\Services\Reports\Search\SearchModes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

/** P6.2: the loan report's classification, partnership and funded-amount filters. */
final class LoanFiltersTest extends TestCase
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
        app()->setLocale('en');
        $this->account = ApplicationFixture::account();
        FinanceFixture::grant($this->account);
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = FinanceFixture::catalog(1);
        $this->actingAs($this->account);
    }

    /** A financed project with a loan, classified by catalog rows made on demand. */
    private function loan(array $classification = [], array $loan = [], ?int $partnership = null): int
    {
        $project = FinanceFixture::acceptedProject($this->account, $this->intake);
        FinanceFixture::withDonor($this->account, $project, $this->catalog['donors']['a']);
        $finance = app(FinanceService::class);
        $finance->saveLoan($this->account, $project, FinanceFixture::loanInput($loan), $finance->show($this->account, $project)['version']);
        $tables = app(OperationalTables::class);
        $update = [];
        foreach ($classification as $table => [$relation, $name]) {
            $row = DB::table('op_'.$table)->where('c_Name', $name)->value('id') ?? ApplicationFixture::row($table, 1, ['c_ID' => DB::table('op_'.$table)->count() + 1, 'c_Name' => $name] + (Schema::hasColumn('op_'.$table, 'c_Name_en') ? ['c_Name_en' => $name] : []));
            $update[$tables->relation('microproject', $relation)['resolved_column']] = $row;
        }
        if ($partnership !== null) {
            $update['c_Partnership'] = $partnership;
        }
        if ($update !== []) {
            DB::table('op_microproject')->where('id', $project)->update($update);
        }

        return $project;
    }

    private function numbers(array $filters): array
    {
        $mode = SearchModes::make('loans');
        $lines = $mode->page($this->account, ['mode' => 'loans'] + $filters)['rows']->getCollection()->all();
        $numbers = array_map(fn ($l) => (int) $l[1], $lines);
        sort($numbers);

        return $numbers;
    }

    private function number(int $project): int
    {
        return (int) DB::table('op_microproject')->where('id', $project)->value('c_MP_ID');
    }

    public function test_each_classification_filter_matches_by_name_and_several_names_match_any(): void
    {
        $a = $this->loan(['fundtype' => ['MP_FundType_ID', 'منحة'], 'microprojecttype' => ['MP_Type_ID', 'زراعي'], 'microprojectsubtype' => ['MP_SubType_ID', 'ري'], 'category' => ['Category_ID', 'حرف'], 'subcategory' => ['SubCategory_ID', 'خشب'], 'career' => ['Career_ID', 'نجار']]);
        $b = $this->loan(['fundtype' => ['MP_FundType_ID', 'قرض'], 'microprojecttype' => ['MP_Type_ID', 'صناعي'], 'microprojectsubtype' => ['MP_SubType_ID', 'ورشة'], 'category' => ['Category_ID', 'خدمات'], 'subcategory' => ['SubCategory_ID', 'صيانة'], 'career' => ['Career_ID', 'سباك']]);
        $c = $this->loan();
        [$na, $nb, $nc] = [$this->number($a), $this->number($b), $this->number($c)];
        self::assertSame([$na, $nb, $nc], $this->numbers([]));
        foreach ([['fund', 'منحة', 'قرض'], ['type', 'زراعي', 'صناعي'], ['subtype', 'ري', 'ورشة'], ['category', 'حرف', 'خدمات'], ['subcategory', 'خشب', 'صيانة'], ['career', 'نجار', 'سباك']] as [$key, $first, $second]) {
            self::assertSame([$na], $this->numbers([$key => [$first]]), "$key one name");
            self::assertSame([$nb], $this->numbers([$key => [$second]]), "$key other name");
            self::assertSame([$na, $nb], $this->numbers([$key => [$first, $second]]), "$key any of several");
            self::assertSame([], $this->numbers([$key => ['لا يوجد']]), "$key unknown name");
            self::assertSame([$na, $nb, $nc], $this->numbers([$key => ['']]), "$key blank means no filter");
        }
        self::assertSame([$na], $this->numbers(['fund' => ['منحة', 'قرض'], 'career' => ['نجار']]), 'different lists combine with and');
        self::assertSame([], $this->numbers(['fund' => ['منحة'], 'career' => ['سباك']]));
        self::assertSame([$na], $this->numbers(['category' => ['حرف'], 'state' => 'Financed', 'donor' => 'Synthetic donor A']));
    }

    public function test_partnership_separates_individual_projects_from_partnerships(): void
    {
        $solo = $this->loan([], [], 1);
        $pair = $this->loan([], [], 2);
        $trio = $this->loan([], [], 3);
        $unknown = $this->loan();
        self::assertSame([$this->number($solo)], $this->numbers(['partnership' => 'individual']));
        self::assertSame([$this->number($pair), $this->number($trio)], $this->numbers(['partnership' => 'partnership']));
        self::assertGreaterThanOrEqual(1, count(array_diff($this->numbers([]), $this->numbers(['partnership' => 'individual']), $this->numbers(['partnership' => 'partnership']))), 'a project with no partnership value is in neither group');
        self::assertContains($this->number($unknown), $this->numbers(['partnership' => '']));
    }

    public function test_funded_amount_range_is_in_usd_inclusive_open_ended_and_skips_loans_without_a_rate(): void
    {
        $usd = $this->loan(['category' => ['Category_ID', 'أ']], ['currency' => 'USD', 'amount' => '1000', 'exchange_rate' => '15000']);
        $sp = $this->loan([], ['currency' => 'SP', 'amount' => '30000000', 'exchange_rate' => '15000']);
        $noRate = $this->loan([], ['currency' => 'SP', 'amount' => '500', 'exchange_rate' => '0']);
        [$nu, $ns, $nz] = [$this->number($usd), $this->number($sp), $this->number($noRate)];
        self::assertSame([$nu, $ns, $nz], $this->numbers([]));
        self::assertSame([$ns], $this->numbers(['funded_from' => '1500']));
        self::assertSame([$nu], $this->numbers(['funded_to' => '1500']));
        self::assertSame([$nu], $this->numbers(['funded_from' => '1000', 'funded_to' => '1000']), 'both ends inclusive');
        self::assertSame([$nu, $ns], $this->numbers(['funded_from' => '1000', 'funded_to' => '2000']));
        self::assertSame([], $this->numbers(['funded_from' => '3000']), 'a blank "to" is open, not 0');
        self::assertSame([$nu], $this->numbers(['funded_from' => '1000', 'funded_to' => '1999.99', 'category' => ['أ']]));
        self::assertNotContains($nz, $this->numbers(['funded_from' => '0']), 'a loan with no exchange rate has no USD value to range over');
    }

    public function test_a_range_may_be_open_at_either_end_over_http(): void
    {
        $this->loan([], ['currency' => 'USD', 'amount' => '1000', 'exchange_rate' => '15000']);
        foreach (['request_to=2027-01-01', 'request_from=2020-01-01', 'loan_to=2027-01-01', 'loan_from=2020-01-01', 'funded_to=5000', 'funded_from=10'] as $open) {
            $this->get('/en/search-reports?mode=loans&'.$open)->assertOk()->assertSessionHasNoErrors()->assertSee('Rows: 1.');
            $this->get('/en/search-reports/export?mode=loans&format=csv&'.$open)->assertOk()->assertSessionHasNoErrors();
        }
        $this->get('/en/search-reports?mode=loans&funded_to=0')->assertOk()->assertSessionHasNoErrors()->assertSee('Rows: 0.');
        // A closed range still has to be in order.
        $this->get('/en/search-reports?mode=loans&request_from=2026-02-01&request_to=2026-01-01')->assertSessionHasErrors('request_to');
        $this->get('/en/search-reports?mode=loans&loan_from=2026-02-01&loan_to=2026-01-01')->assertSessionHasErrors('loan_to');
        $this->get('/en/search-reports?mode=loans&funded_from=9&funded_to=3')->assertSessionHasErrors('funded_to');
    }

    public function test_the_page_lists_the_names_keeps_the_choices_and_the_exports_follow_them(): void
    {
        $a = $this->loan(['fundtype' => ['MP_FundType_ID', 'منحة'], 'career' => ['Career_ID', 'نجار']], ['currency' => 'USD', 'amount' => '1000', 'exchange_rate' => '15000'], 1);
        $this->loan(['fundtype' => ['MP_FundType_ID', 'قرض']], ['currency' => 'USD', 'amount' => '5000', 'exchange_rate' => '15000'], 2);
        $query = 'mode=loans&fund[]=%D9%85%D9%86%D8%AD%D8%A9&partnership=individual&funded_from=500&funded_to=2000';
        $page = $this->get('/en/search-reports?'.$query)->assertOk()->assertSee('Rows: 1.')->assertSee('Fund type')->assertSee('Partnership')->assertSee('Funded amount from (USD)')->assertSee('قرض')->assertSee('نجار');
        $page->assertSee('name="fund[]"', false)->assertSee('value="منحة" selected', false)->assertSee('value="individual" selected', false)->assertSee('value="500"', false);
        $csv = $this->get('/en/search-reports/export?'.$query.'&format=csv')->assertOk()->streamedContent();
        self::assertStringContainsString((string) $this->number($a), $csv);
        self::assertCount(2, array_filter(explode("\r\n", $csv)), 'header and one loan');
        self::assertStringContainsString('fund%5B0%5D=', $this->get('/en/search-reports?'.$query)->getContent());
        $this->get('/ar/search-reports?mode=loans')->assertOk()->assertSee('نوع التمويل')->assertSee('الشراكة');
        $this->get('/en/search-reports?mode=loans&funded_from=9&funded_to=3')->assertSessionHasErrors('funded_to');
        $this->get('/en/search-reports?mode=loans&funded_from=-1')->assertSessionHasErrors('funded_from');
        $this->get('/en/search-reports?mode=loans&partnership=both')->assertSessionHasErrors('partnership');
        $this->get('/en/search-reports?mode=family')->assertOk()->assertDontSee('Fund type')->assertDontSee('Funded amount');
    }
}
