<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Staff\TaskBoardService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class TaskBoardTest extends TestCase
{
    private WebAccount $account;

    private TaskBoardService $board;

    private array $intake;

    /** @var array<int, int> legacy task id => task row id (city 1) */
    private array $tasks = [];

    private int $projectA;

    private int $projectB;

    private array $cells = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        StaffFixture::grant($this->account);
        $this->board = app(TaskBoardService::class);
        $this->intake = ApplicationFixture::intake(1);
        foreach ([3 => 'Story (English)', 16 => 'Purchases', 25 => 'Opening visit'] as $legacy => $name) {
            $this->tasks[$legacy] = DB::table('op_task')->where('city_id', 1)->where('c_T_ID', $legacy)->value('id') ?? StaffFixture::task(1, $legacy, $name);
        }
        $this->tasks[39] = StaffFixture::task(1, 39, 'Denomination', 'a');
        $this->projectA = FinanceFixture::acceptedProject($this->account, $this->intake);
        $this->projectB = FinanceFixture::acceptedProject($this->account, $this->intake);
        DB::table('op_task_microproject')->delete();
        foreach ([$this->projectA, $this->projectB] as $project) {
            foreach ([3, 16, 25, 39] as $legacy) {
                $this->cells[$project][$legacy] = StaffFixture::assignTask($project, $this->tasks[$legacy]);
            }
        }
    }

    private function state(int $project, string $name): void
    {
        $mp = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_microproject_state' && $r['child_columns'] === ['MicroProject_ID']);
        $st = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_microproject_state' && $r['child_columns'] === ['State_ID']);
        DB::table('op_microproject_state')->where($mp['resolved_column'], $project)->where('c_Current_State', 1)->update([$st['resolved_column'] => $this->intake['states'][$name]]);
    }

    private function responsible(int $legacy = 16): int
    {
        return StaffFixture::responsible($this->account, 1, $this->tasks[$legacy]);
    }

    private function toggle(int $project, int $legacy, int $expected, int $done): void
    {
        $this->board->toggle($this->account, $project, $legacy, $expected, $done);
    }

    private function assertStatus(int $status, callable $call): void
    {
        try {
            $call();
            self::fail('Expected HTTP '.$status);
        } catch (HttpException $e) {
            self::assertSame($status, $e->getStatusCode());
        }
    }

    private function rows(array $filters = []): array
    {
        return $this->board->board($this->account, $filters)['rows']->getCollection()->keyBy('id')->all();
    }

    public function test_the_board_lists_funded_projects_with_task_cells_and_the_application_board_lists_all(): void
    {
        DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->update(['c_State' => 1]);
        $this->state($this->projectB, 'Waiting');
        $rows = $this->rows();
        self::assertSame([$this->projectA], array_keys($rows), 'a waiting application is not on the funded board');
        $row = $rows[$this->projectA];
        self::assertTrue($row->cells[3]['done']);
        self::assertFalse($row->cells[16]['done']);
        self::assertTrue($row->cells[16]['assigned']);
        self::assertFalse($row->cells[4]['assigned'], 'a task the project does not have stays empty');
        self::assertFalse($row->cells[3]['manageable']);
        self::assertContains('Synthetic Applicant ابن/ة', array_map(fn ($name) => rtrim($name), $row->people_names));

        $applications = $this->rows(['kind' => 'a']);
        self::assertEqualsCanonicalizing([$this->projectA, $this->projectB], array_keys($applications));
        self::assertTrue($applications[$this->projectA]->cells[39]['assigned']);
    }

    public function test_only_the_assigned_account_toggles_a_task_and_stale_or_repeated_submissions_are_refused(): void
    {
        $this->assertStatus(403, fn () => $this->toggle($this->projectA, 3, 0, 1));
        $this->responsible(3);
        self::assertTrue($this->rows()[$this->projectA]->cells[3]['manageable']);
        self::assertFalse($this->rows()[$this->projectA]->cells[16]['manageable'], 'responsible for one task only');
        $this->toggle($this->projectA, 3, 0, 1);
        $cell = DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->first();
        self::assertSame(1, (int) $cell->c_State);
        self::assertSame(now()->toDateString().' 00:00:00', $cell->c_Date);
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('id', $this->cells[$this->projectB][3])->value('c_State'), 'other projects are untouched');

        $this->assertStatus(409, fn () => $this->toggle($this->projectA, 3, 0, 1));
        $this->toggle($this->projectA, 3, 1, 0);
        self::assertSame(0, (int) DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->value('c_State'));
        $this->assertStatus(409, fn () => $this->toggle($this->projectA, 3, 0, 0));
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'tasks.toggled')->count());
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'access.denied')->count());
    }

    public function test_responsibility_is_per_account_and_city_and_can_be_withdrawn(): void
    {
        $assignment = $this->responsible(3);
        self::assertTrue($this->rows()[$this->projectA]->cells[3]['manageable']);

        DB::table('account_city_tasks')->where('id', $assignment)->update(['revoked_at' => now()]);
        self::assertFalse($this->rows()[$this->projectA]->cells[3]['manageable'], 'a withdrawn assignment carries no responsibility');
        DB::table('account_city_tasks')->where('id', $assignment)->update(['revoked_at' => null]);

        $colleague = StaffFixture::account('colleague');
        StaffFixture::grant($colleague, [1, 2], [...StaffFixture::CAPABILITIES, 'applications.view']);
        self::assertFalse($this->board->board($colleague, [])['rows']->getCollection()->first()->cells[3]['manageable'], 'an assignment belongs to one account');
        StaffFixture::responsible($colleague, 2, StaffFixture::task(2, 3, 'Story'));
        self::assertFalse($this->board->board($colleague, ['cities' => [1]])['rows']->getCollection()->first()->cells[3]['manageable'], 'nor does an assignment in another city');
    }

    public function test_the_manage_all_capability_lets_an_administrator_toggle_any_task_of_the_city_only(): void
    {
        $this->assertStatus(403, fn () => $this->toggle($this->projectA, 25, 0, 1));
        StaffFixture::grant($this->account, [1], ['tasks.manage_all']);
        $this->toggle($this->projectA, 25, 0, 1);
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][25])->value('c_State'));
        DB::table('account_city_capabilities')->where('capability', 'tasks.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->toggle($this->projectA, 25, 1, 0));
        $this->assertStatus(404, fn () => $this->toggle($this->projectA, 999, 0, 1));
    }

    public function test_a_project_off_the_board_or_without_the_task_cannot_be_toggled(): void
    {
        StaffFixture::grant($this->account, [1], ['tasks.manage_all']);
        $this->state($this->projectB, 'Waiting');
        $this->assertStatus(404, fn () => $this->toggle($this->projectB, 3, 0, 1));
        $this->toggle($this->projectB, 39, 0, 1); // the application board lists every current state
        DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->delete();
        $this->assertStatus(404, fn () => $this->toggle($this->projectA, 3, 0, 1));
        $this->assertStatus(404, fn () => $this->toggle($this->projectA, 4, 0, 1));
    }

    public function test_completing_purchases_notifies_communication_accounts_and_clearing_it_marks_only_that_project_seen(): void
    {
        StaffFixture::grant($this->account, [1], ['tasks.manage_all']);
        $one = StaffFixture::account('comm.one', [1 => ['communication']]);
        $two = StaffFixture::account('comm.two', [1 => ['communication']]);
        StaffFixture::account('finance.only', [1 => ['financial']]);
        StaffFixture::account('comm.elsewhere', [2 => ['communication']]);
        $this->toggle($this->projectA, 16, 0, 1);
        $this->toggle($this->projectB, 16, 0, 1);
        $notices = DB::table('web_notices')->where('kind', 'purchases')->orderBy('id')->get();
        self::assertCount(4, $notices);
        $first = $notices->first();
        self::assertSame('انتهاء المشتريات', $first->body);
        self::assertSame(0, (int) $first->seen);
        self::assertSame(now()->toDateString(), substr((string) $first->notice_date, 0, 10));
        self::assertContains('Synthetic Applicant ابن/ة', array_map('rtrim', explode(',', $first->title)));
        self::assertEqualsCanonicalizing([$one->id, $two->id], $notices->pluck('account_id')->map(fn ($id) => (int) $id)->unique()->all(), 'communication accounts of this city only');
        self::assertSame($this->account->id, (int) $first->sender_account_id);
        self::assertSame(1, (int) $first->city_id);

        $this->toggle($this->projectA, 16, 1, 0);
        self::assertSame([1, 1], DB::table('web_notices')->where('project_id', $this->projectA)->where('kind', 'purchases')->pluck('seen')->map(fn ($v) => (int) $v)->all());
        self::assertSame([0, 0], DB::table('web_notices')->where('project_id', $this->projectB)->where('kind', 'purchases')->pluck('seen')->map(fn ($v) => (int) $v)->all());
        $this->toggle($this->projectA, 3, 0, 1);
        self::assertSame(4, DB::table('web_notices')->where('kind', 'purchases')->count(), 'other tasks send nothing');
    }

    public function test_filters_narrow_the_board_and_only_permitted_cities_appear(): void
    {
        DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->update(['c_State' => 1]);
        self::assertSame([$this->projectA], array_keys($this->rows(['tasks' => [3 => '1']])));
        self::assertSame([$this->projectB], array_keys($this->rows(['tasks' => [3 => '0']])));
        self::assertSame([], $this->rows(['tasks' => [4 => '1']]), 'a task nobody has matches nothing');
        self::assertEqualsCanonicalizing([$this->projectA, $this->projectB], array_keys($this->rows(['state' => 1])));
        self::assertSame([], $this->rows(['state' => 5]));
        self::assertSame([$this->projectB], array_keys($this->rows(['q' => (string) DB::table('op_microproject')->where('id', $this->projectB)->value('c_MP_ID')])));
        self::assertEqualsCanonicalizing([$this->projectA, $this->projectB], array_keys($this->rows(['q' => 'Applicant'])));
        self::assertSame([], $this->rows(['q' => '100%']));
        self::assertSame([], $this->rows(['type' => 'No such type']));
        self::assertEqualsCanonicalizing([$this->projectA, $this->projectB], array_keys($this->rows(['type' => 'Synthetic type'])));

        $other = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(2), 2);
        self::assertContains($other, array_keys($this->rows()));
        self::assertSame([$other], array_keys($this->rows(['cities' => [2]])));
        DB::table('account_city_capabilities')->where('city_id', 2)->where('capability', 'tasks.view')->update(['revoked_at' => now()]);
        self::assertNotContains($other, array_keys($this->rows(['cities' => [1, 2]])));
        DB::table('account_city_capabilities')->where('capability', 'tasks.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->board->board($this->account, []));
    }

    public function test_pages_and_posts_work_over_http_in_both_languages(): void
    {
        $this->responsible(3);
        $this->actingAs($this->account);
        $this->get('/en/task-board')->assertOk()->assertSee('Task board')->assertSee('Synthetic task 3')->assertSee('Mark done')->assertSee('synthetic.applications');
        $this->get('/ar/task-board?kind=a')->assertOk()->assertSee('dir="rtl"', false)->assertSee('لوحة المهام');
        $this->get('/en/task-board?kind=x')->assertSessionHasErrors('kind');
        $payload = ['project' => $this->projectA, 'task' => 3, 'expected' => 0, 'done' => 1];
        $this->post('/en/task-board/toggle', $payload)->assertRedirect();
        self::assertSame(1, (int) DB::table('op_task_microproject')->where('id', $this->cells[$this->projectA][3])->value('c_State'));
        $this->post('/en/task-board/toggle', $payload)->assertStatus(409);
        $this->post('/en/task-board/toggle', ['project' => $this->projectA, 'task' => 16, 'expected' => 0, 'done' => 1])->assertForbidden();
        $this->post('/en/task-board/toggle', ['project' => 'x', 'task' => 3, 'expected' => 0, 'done' => 1])->assertSessionHasErrors('project');
        DB::table('account_city_capabilities')->where('capability', 'tasks.view')->update(['revoked_at' => now()]);
        $this->get('/en/task-board')->assertForbidden();
        $this->get('/en/applications')->assertOk()->assertDontSee('Task board');
    }
}
