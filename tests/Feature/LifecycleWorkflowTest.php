<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FullApplicationFixture;
use Tests\TestCase;

final class LifecycleWorkflowTest extends TestCase
{
    private WebAccount $account;

    private ApplicationService $applications;

    private LifecycleService $lifecycle;

    private array $intake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->applications = app(ApplicationService::class);
        $this->lifecycle = app(LifecycleService::class);
        $this->intake = [1 => ApplicationFixture::intake(1), 2 => ApplicationFixture::intake(2)];
    }

    public function test_preliminary_creation_records_on_hold_as_the_only_current_state(): void
    {
        $id = $this->create();
        $panel = $this->lifecycle->panel($this->account, $id);
        self::assertCount(1, $panel['history']);
        self::assertSame('On Hold', $panel['current']->state_name_en);
        self::assertSame(1, (int) $panel['current']->c_Current_State);
        self::assertSame(0, DB::table('op_task_microproject')->count());
    }

    public function test_creation_fails_without_writing_when_the_state_catalog_lacks_on_hold(): void
    {
        DB::table('op_state')->where('c_Name_en', 'On Hold')->where('city_id', 1)->delete();
        $this->assertStatus(409, fn () => $this->create());
        self::assertSame(0, DB::table('op_microproject')->count());
        self::assertSame(1000, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
    }

    public function test_manual_transitions_follow_the_desktop_exclusions_and_keep_one_current_row(): void
    {
        $id = $this->create();
        $states = $this->intake[1]['states'];
        $version = fn () => $this->lifecycle->panel($this->account, $id)['version'];
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $states['On Hold'], null, null, $version()));
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $states['Waiting'], null, null, $version()));
        // Identifier 0 (Rejected) is a valid state, not "no state".
        $this->lifecycle->transition($this->account, $id, $states['Rejected'], $this->intake[1]['reason'], 'exact  note ', $version());
        $panel = $this->lifecycle->panel($this->account, $id);
        self::assertSame('Rejected', $panel['current']->state_name_en);
        self::assertSame('exact  note ', $panel['current']->c_Note);
        self::assertSame('Synthetic reason', $panel['current']->reason_name);
        self::assertSame(2, $panel['history']->count());
        self::assertSame(1, $panel['history']->filter(fn ($row) => (int) $row->c_Current_State === 1)->count());
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $states['Rejected'], null, null, $version()));
        $this->lifecycle->transition($this->account, $id, $states['Waiting'], null, null, $version());
        self::assertSame('Waiting', $this->lifecycle->panel($this->account, $id)['current']->state_name_en);
    }

    public function test_stale_versions_and_cross_city_states_are_rejected(): void
    {
        $id = $this->create();
        $stale = $this->lifecycle->panel($this->account, $id)['version'];
        $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Under Review'], null, null, $stale);
        $this->assertStatus(409, fn () => $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Delayed'], null, null, $stale));
        $version = $this->lifecycle->panel($this->account, $id)['version'];
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $this->intake[2]['states']['Delayed'], null, null, $version));
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Delayed'], $this->intake[2]['reason'], null, $version));
        self::assertSame(2, $this->lifecycle->panel($this->account, $id)['history']->count());
    }

    public function test_accepted_needs_its_own_capability_assigns_final_tasks_once_and_blocks_direct_financing(): void
    {
        $id = $this->create();
        $states = $this->intake[1]['states'];
        $version = fn () => $this->lifecycle->panel($this->account, $id)['version'];
        DB::table('account_city_capabilities')->where('capability', 'lifecycle.accept')->update(['revoked_at' => now()]);
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $states['Accepted'], null, null, $version()));
        self::assertNotContains($states['Accepted'], $this->lifecycle->panel($this->account, $id)['targets']->pluck('id')->all());
        DB::table('account_city_capabilities')->where('capability', 'lifecycle.accept')->update(['revoked_at' => null]);
        $this->lifecycle->transition($this->account, $id, $states['Accepted'], null, null, $version());
        self::assertSame([3 => 0, 4 => 0], $this->taskStates($id));
        $this->assertStatus(422, fn () => $this->lifecycle->transition($this->account, $id, $states['Financed'], null, null, $version()));
        $this->lifecycle->transition($this->account, $id, $states['Delayed'], null, null, $version());
        $this->lifecycle->transition($this->account, $id, $states['Accepted'], null, null, $version());
        self::assertCount(2, DB::table('op_task_microproject')->get());
    }

    public function test_withdrawal_clears_a_set_donor_reference_only(): void
    {
        $id = $this->create();
        $version = $this->lifecycle->panel($this->account, $id)['version'];
        $donor = ApplicationFixture::row('donor', 1, ['c_ID' => 5, 'c_Name' => 'Synthetic donor']);
        $link = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_microproject' && $r['child_columns'] === ['MP_Donor']);
        DB::table('op_microproject')->where('id', $id)->update([$link['resolved_column'] => $donor, $link['state_column'] => 'resolved']);
        $version = $this->lifecycle->panel($this->account, $id)['version'];
        $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Withdrew'], null, null, $version);
        self::assertNull(DB::table('op_microproject')->where('id', $id)->value($link['resolved_column']));
        self::assertSame('null', DB::table('op_microproject')->where('id', $id)->value($link['state_column']));
    }

    public function test_reason_and_note_update_only_the_current_row(): void
    {
        $id = $this->create();
        $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Delayed'], null, 'first', $this->lifecycle->panel($this->account, $id)['version']);
        $this->lifecycle->updateCurrent($this->account, $id, $this->intake[1]['reason'], 'second', $this->lifecycle->panel($this->account, $id)['version']);
        $history = $this->lifecycle->panel($this->account, $id)['history'];
        self::assertSame('second', $history->first()->c_Note);
        self::assertSame('Synthetic reason', $history->first()->reason_name);
        self::assertNull($history->last()->c_Note);
    }

    public function test_grants_gate_reads_and_writes_per_city(): void
    {
        $id = $this->create();
        $version = $this->lifecycle->panel($this->account, $id)['version'];
        DB::table('account_city_capabilities')->where('capability', 'lifecycle.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->lifecycle->transition($this->account, $id, $this->intake[1]['states']['Delayed'], null, null, $version));
        $panel = $this->lifecycle->panel($this->account, $id);
        self::assertFalse($panel['canManage']);
        self::assertTrue($panel['targets']->isEmpty());
        DB::table('account_city_capabilities')->where('capability', 'applications.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->lifecycle->panel($this->account, $id));
        self::assertSame(1, DB::table('op_microproject_state')->count());
    }

    public function test_full_conversion_hands_off_to_waiting_and_initial_tasks_exactly_once(): void
    {
        $id = $this->create();
        $this->assertStatus(422, fn () => $this->applications->convertToFull($this->account, $id, $this->applications->find($this->account, $id)['version']));
        $this->completeApplication($id);
        $this->applications->convertToFull($this->account, $id, $this->applications->find($this->account, $id)['version']);
        self::assertSame('project', DB::table('op_microproject')->where('id', $id)->value('c_ApplicationState'));
        $panel = $this->lifecycle->panel($this->account, $id);
        self::assertSame(['On Hold', 'Waiting'], $panel['history']->sortBy('id')->pluck('state_name_en')->values()->all());
        self::assertSame('Waiting', $panel['current']->state_name_en);
        self::assertSame([1 => 1, 2 => 0], $this->taskStates($id));
        $this->assertStatus(422, fn () => $this->applications->convertToFull($this->account, $id, $this->applications->find($this->account, $id)['version']));
        self::assertCount(2, DB::table('op_task_microproject')->get());
    }

    public function test_full_conversion_requires_a_complete_budget(): void
    {
        $id = $this->create();
        $this->completeApplication($id, false);
        $this->assertStatus(422, fn () => $this->applications->convertToFull($this->account, $id, $this->applications->find($this->account, $id)['version']));
        $this->applications->saveDetail($this->account, $id, 'material', ['c_Name' => 'Item'], $this->applications->find($this->account, $id)['version']);
        $this->assertStatus(422, fn () => $this->applications->convertToFull($this->account, $id, $this->applications->find($this->account, $id)['version']));
        self::assertSame('zero', DB::table('op_microproject')->where('id', $id)->value('c_ApplicationState'));
        self::assertSame(1, DB::table('op_microproject_state')->count());
    }

    private function completeApplication(int $id, bool $budget = true): void
    {
        FullApplicationFixture::complete($id, $this->intake[1]['person'], $this->intake[1]);
        if (! $budget) {
            DB::table('op_material')->delete();
        }
    }

    /** @return array<int, int> raw task id => state */
    private function taskStates(int $project): array
    {
        $result = [];
        foreach (DB::table('op_task_microproject as t')->join('op_task as k', 'k.id', '=', 't.ref_bbc62ceac64e_id')->where('t.ref_cb73618c9799_id', $project)->get(['k.c_T_ID', 't.c_State']) as $row) {
            $result[(int) $row->c_T_ID] = (int) $row->c_State;
        }
        ksort($result);

        return $result;
    }

    private function create(int $city = 1): int
    {
        return $this->applications->create($this->account, $city, ['c_MP_Name' => 'Synthetic application', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[$city]['person'], $this->intake[$city]['references']);
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
