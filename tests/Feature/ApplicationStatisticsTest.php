<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Operational\OperationalTables;
use App\Services\Reports\ApplicationReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/** P6.1 on invented data: filters, permitted cross-city counts and coverage. */
final class ApplicationStatisticsTest extends TestCase
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
        foreach ([1, 2] as $city) {
            $this->intake[$city] = ApplicationFixture::intake($city);
        }
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        // City 1: two 2025 applications (one Accepted) and one 2026 application with no date in 2026-02; city 2: one 2026 application.
        $this->application(1, '2025-03-01', 'Accepted');
        $this->application(1, '2025-12-31');
        $this->application(1, '2026-01-01');
        $this->application(2, '2026-02-10', 'Accepted');
        $this->application(2, null);
    }

    public function test_totals_are_counted_across_permitted_cities_with_breakdowns_and_coverage(): void
    {
        $stats = app(ApplicationReport::class)->statistics($this->account, [], $this->none());

        self::assertSame(5, $stats['total']);
        self::assertSame([1 => 3, 2 => 2], $stats['byCity']);
        self::assertSame(['zero' => 5, 'project' => 0, 'other' => 0], $stats['byStage']);
        self::assertSame(['2025' => 2, '2026' => 2], $stats['byYear']);
        self::assertSame(1, $stats['undated']);
        self::assertSame(['Accepted' => 2, 'On Hold' => 3], $stats['byState']);
        self::assertSame(1, $stats['matrix']['Accepted'][1]);
        self::assertSame(1, $stats['matrix']['Accepted'][2]);
        self::assertSame([true, true], $stats['coverage']->pluck('included')->all());
    }

    public function test_filters_narrow_the_counts_and_the_list_the_same_way(): void
    {
        $cases = [
            ['state' => 'Accepted', 'expected' => 2],
            ['date_from' => '2025-12-31', 'date_to' => '2026-01-01', 'expected' => 2],
            ['date_from' => '2026-01-01', 'expected' => 2],
            ['date_to' => '2025-12-31', 'expected' => 2],
            ['state' => 'Accepted', 'date_from' => '2026-01-01', 'expected' => 1],
            ['stage' => 'project', 'expected' => 0],
            ['state' => 'No such state', 'expected' => 0],
        ];
        foreach ($cases as $case) {
            $filters = array_replace($this->none(), array_diff_key($case, ['expected' => 1]));
            $stats = app(ApplicationReport::class)->statistics($this->account, [], $filters);
            $list = app(ApplicationService::class)->search($this->account, [], '', $filters);
            self::assertSame($case['expected'], $stats['total'], json_encode($case));
            self::assertSame($case['expected'], $list->total(), json_encode($case));
        }
    }

    public function test_an_unselected_or_revoked_city_is_never_counted_and_a_forbidden_selection_is_refused(): void
    {
        $this->actingAs($this->account);
        $stats = app(ApplicationReport::class)->statistics($this->account, [2], $this->none());
        self::assertSame(2, $stats['total']);
        self::assertSame([false, true], $stats['coverage']->pluck('included')->all());

        DB::table('account_city_capabilities')->where('city_id', 2)->where('capability', 'applications.view')->update(['revoked_at' => now('UTC')]);
        $stats = app(ApplicationReport::class)->statistics($this->account, [], $this->none());
        self::assertSame(3, $stats['total']);
        self::assertSame([1], $stats['coverage']->pluck('id')->all());
        $this->get('/en/statistics?cities[]=2')->assertForbidden();
        $this->get('/en/statistics')->assertOk()->assertDontSee('Damascus')->assertSee('Aleppo');
    }

    public function test_pages_render_in_both_languages_and_require_sign_in(): void
    {
        $this->get('/en/statistics')->assertRedirect('/en/login');
        $this->actingAs($this->account);
        $this->get('/en/statistics?state=Accepted&date_from=2026-01-01')->assertOk()->assertSee('Application statistics')->assertSee('Lifecycle status')
            ->assertSee('/ar/statistics?', false)->assertSee('state=Accepted', false);
        $this->get('/ar/statistics')->assertOk()->assertSee('dir="rtl"', false)->assertSee('إحصائيات الطلبات');
        $this->get('/en/applications?state=Accepted')->assertOk()->assertSee('Lifecycle status');
    }

    public function test_invalid_filters_are_rejected_and_empty_results_are_explained(): void
    {
        $this->actingAs($this->account);
        foreach (['date_from=2026-13-01', 'date_to=2025-01-01&date_from=2026-01-01', 'stage=other', 'cities[]=9'] as $query) {
            $this->from('/en/statistics')->get('/en/statistics?'.$query)->assertRedirect('/en/statistics');
        }
        $this->get('/en/statistics?state=No+such+state')->assertOk()->assertSee('No matching applications');
    }

    private function none(): array
    {
        return ['stage' => '', 'state' => '', 'date_from' => '', 'date_to' => ''];
    }

    private function application(int $city, ?string $date, ?string $state = null): int
    {
        // New entries require a date; an imported legacy row may lack one, so the date is cleared afterwards.
        $fields = ['c_MP_Name' => 'Synthetic '.$city, 'c_MP_DateOfRequest' => $date ?? '2026-01-01'];
        $id = app(ApplicationService::class)->create($this->account, $city, $fields, $this->intake[$city]['person'], $this->intake[$city]['references']);
        if ($date === null) {
            DB::table('op_microproject')->where('id', $id)->update(['c_MP_DateOfRequest' => null]);
        }
        if ($state !== null) {
            $relation = app(OperationalTables::class)->relation('microproject_state', 'State_ID');
            DB::table('op_microproject_state')->where($this->parent(), $id)->update([$relation['resolved_column'] => $this->intake[$city]['states'][$state]]);
        }

        return $id;
    }

    private function parent(): string
    {
        return app(OperationalTables::class)->relation('microproject_state', 'MicroProject_ID')['resolved_column'];
    }
}
