<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Staff\TaskBoardColumns;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** P6.2: visit-schedule and task-board exports return every matching row (all pages), inside the page's own authorization. */
final class FieldExportTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private int $project;

    /** @var array<int, int> */
    private array $tasks = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        StaffFixture::grant($this->account);
        FieldFixture::grant($this->account);
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        $this->actingAs($this->account);
    }

    public function test_visit_schedule_export_lists_every_visit_with_status_and_follows_the_filters(): void
    {
        $visit = fn (string $body, string $date, array $fields = []) => StaffFixture::notice(1, null, 'visit', $body, $date, $fields, $this->project);
        $visit('Visit Opening-O', '2026-11-05', ['note' => 'هاتف أولاً']);
        $visit('Visit M1-O', '2027-05-05');
        $visit('Visit Closing-O', '2027-11-05', ['seen' => 1]);
        $visit('Visit Opening-V', '2026-12-01');
        foreach (range(1, 55) as $n) {
            $visit('Visit M2-O', '2028-01-'.str_pad((string) (($n % 28) + 1), 2, '0', STR_PAD_LEFT));
        }

        [$headers, $rows] = $this->csv('/en/visit-schedule/export');
        self::assertSame(['City', 'Project number', 'Project name', 'Beneficiary', 'Visit', 'Date', 'Seen', 'Visit status', 'Note'], $rows[0]);
        self::assertCount(59, array_slice($rows, 1), 'all pages, not the 50 of one page');
        self::assertSame('text/csv; charset=UTF-8', $headers->get('content-type'));
        self::assertSame('2026-11-05', substr($rows[1][5], 0, 10));
        self::assertSame(['Visit Opening-O', 'Not seen', 'هاتف أولاً'], [$rows[1][4], $rows[1][6], $rows[1][8]]);
        $closing = collect(array_slice($rows, 1))->firstWhere(4, 'Visit Closing-O');
        self::assertSame('Seen', $closing[6]);

        [, $kind] = $this->csv('/en/visit-schedule/export?kind=V');
        self::assertSame(['Visit Opening-V'], array_column(array_slice($kind, 1), 4));
        [, $year] = $this->csv('/en/visit-schedule/export?year=2027');
        self::assertCount(2, array_slice($year, 1));
        [, $seen] = $this->csv('/en/visit-schedule/export?seen=1');
        self::assertSame(['Visit Closing-O'], array_column(array_slice($seen, 1), 4));

        $xlsx = $this->workbook($this->get('/en/visit-schedule/export')->assertOk()->streamedContent());
        self::assertCount(60, $xlsx);
        self::assertSame($rows[1][1] === '' ? '' : (int) $rows[1][1], $xlsx[1][1]);
    }

    public function test_visit_schedule_export_is_refused_without_the_grant_and_never_shows_other_cities(): void
    {
        StaffFixture::notice(1, null, 'visit', 'Visit Opening-O', '2026-11-05', [], $this->project);
        $other = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(2), 2);
        StaffFixture::notice(2, null, 'visit', 'Visit M1-O', '2027-05-05', [], $other);
        [, $both] = $this->csv('/en/visit-schedule/export');
        self::assertCount(2, array_slice($both, 1));
        $before = DB::table('account_audit_events')->where('event_type', 'reports.exported')->count();
        DB::table('account_city_capabilities')->where('city_id', 2)->where('capability', 'visits.view')->update(['revoked_at' => now()]);
        [, $one] = $this->csv('/en/visit-schedule/export?cities[]=2');
        self::assertCount(1, array_slice($one, 1), 'an unpermitted requested city is ignored, as on the page');
        self::assertSame(['Visit Opening-O'], array_column(array_slice($one, 1), 4));
        self::assertSame($before + 1, DB::table('account_audit_events')->where('event_type', 'reports.exported')->count());
        $this->get('/en/visit-schedule/export?format=nope')->assertSessionHasErrors('format');
        DB::table('account_city_capabilities')->where('capability', 'visits.view')->update(['revoked_at' => now()]);
        $this->get('/en/visit-schedule/export')->assertForbidden();
    }

    public function test_task_board_export_has_one_row_per_project_and_a_column_per_task(): void
    {
        foreach ([3 => 'Story (English)', 16 => 'Purchases'] as $legacy => $name) {
            $this->tasks[$legacy] = DB::table('op_task')->where('city_id', 1)->where('c_T_ID', $legacy)->value('id') ?? StaffFixture::task(1, $legacy, $name);
        }
        $second = FinanceFixture::acceptedProject($this->account, $this->intake);
        DB::table('op_task_microproject')->delete();
        $cell = [];
        foreach ([$this->project, $second] as $project) {
            foreach ([3, 16] as $legacy) {
                $cell[$project][$legacy] = StaffFixture::assignTask($project, $this->tasks[$legacy]);
            }
        }
        DB::table('op_task_microproject')->where('id', $cell[$this->project][3])->update(['c_State' => 1]);
        DB::table('op_task_microproject')->where('id', $cell[$second][16])->delete();

        [, $rows] = $this->csv('/en/task-board/export');
        self::assertSame(['City', 'Project number', 'Beneficiary', 'Mobile', 'Project name', 'Status', 'Donor', 'Type', 'Loan date'], array_slice($rows[0], 0, 9));
        $taskHeads = array_slice($rows[0], 9);
        self::assertNotEmpty($taskHeads);
        self::assertCount(2, array_slice($rows, 1));
        $byNumber = array_column(array_slice($rows, 1), null, 1);
        $numberA = (string) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID');
        $numberB = (string) DB::table('op_microproject')->where('id', $second)->value('c_MP_ID');
        self::assertCount(2, $byNumber);
        $order = array_keys(TaskBoardColumns::for('f'));
        self::assertCount(count($order), $taskHeads, 'one column per board task');
        $story = array_search(3, $order, true);
        $purchases = array_search(16, $order, true);
        self::assertSame('Done', $byNumber[$numberA][9 + $story]);
        self::assertSame('Not done', $byNumber[$numberB][9 + $story]);
        self::assertSame('Not done', $byNumber[$numberA][9 + $purchases]);
        self::assertSame('Not assigned', $byNumber[$numberB][9 + $purchases]);
        $unassigned = array_diff(array_keys($order), [$story, $purchases]);
        self::assertSame(['Not assigned'], array_values(array_unique(array_map(fn ($i) => $byNumber[$numberA][9 + $i], $unassigned))));

        [, $filtered] = $this->csv('/en/task-board/export?tasks[3]=1');
        self::assertCount(1, array_slice($filtered, 1));
        [, $apps] = $this->csv('/en/task-board/export?kind=a');
        self::assertNotContains('Loan date', $apps[0]);
        self::assertGreaterThanOrEqual(2, count(array_slice($apps, 1)));
        $xlsx = $this->workbook($this->get('/en/task-board/export')->assertOk()->streamedContent());
        self::assertCount(3, $xlsx);
        self::assertSame(count($rows[0]), count($xlsx[0]));
    }

    public function test_task_board_export_is_refused_without_the_grant_and_audited(): void
    {
        $before = DB::table('account_audit_events')->where('event_type', 'reports.exported')->count();
        $this->csv('/en/task-board/export');
        self::assertSame($before + 1, DB::table('account_audit_events')->where('event_type', 'reports.exported')->count());
        $this->get('/en/task-board/export?format=bad')->assertSessionHasErrors('format');
        DB::table('account_city_capabilities')->where('capability', 'tasks.view')->update(['revoked_at' => now()]);
        $this->get('/en/task-board/export')->assertForbidden();
    }

    public function test_export_links_carry_the_filters_on_both_pages(): void
    {
        $this->get('/en/visit-schedule?year=2027&kind=V')->assertOk()
            ->assertSee('/en/visit-schedule/export?format=xlsx&amp;year=2027&amp;kind=V', false)->assertSee('/en/visit-schedule/export?format=csv&amp;year=2027&amp;kind=V', false)
            ->assertSee('Export all matching visits (Excel)');
        $this->get('/ar/task-board?kind=a')->assertOk()->assertSee('/ar/task-board/export?kind=a&amp;format=xlsx', false)->assertSee('تصدير كل المشاريع المطابقة');
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

    /** @return list<list<mixed>> the first sheet of a downloaded workbook */
    private function workbook(string $content): array
    {
        self::assertStringStartsWith('PK', $content);
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $content);
        $reader = new Reader;
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break;
        }
        $reader->close();
        unlink($path);

        return $rows;
    }
}
