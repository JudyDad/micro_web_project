<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Lifecycle\LifecycleService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/** P4.4: whole intake journeys over HTTP with invented data (Arabic text, sentinels, retries, grants). */
final class IntakeAcceptanceTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $members;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = [1 => ApplicationFixture::intake(1), 2 => ApplicationFixture::intake(2)];
        $this->members = ApplicationFixture::checklistCatalog(1);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->actingAs($this->account);
    }

    public function test_application_journey_from_preliminary_to_accepted_over_http(): void
    {
        $token = $this->get('/ar/applications/create?city_id=1')->assertOk()->viewData('createToken');
        $this->post('/ar/applications', [
            'create_token' => $token, 'city_id' => 1, 'beneficiary_id' => $this->intake[1]['person'], 'references' => $this->intake[1]['references'],
            'fields' => ['c_MP_Name' => 'مشروع خياطة منزلية', 'c_MP_DateOfRequest' => '2026-10-19', 'c_MP_Note' => "  ملاحظة\nبسطرين  "],
        ])->assertRedirect();
        $id = (int) DB::table('op_microproject')->value('id');
        $url = '/en/applications/'.$id;
        $this->get($url)->assertOk()->assertSee('On Hold')->assertSee('مشروع خياطة منزلية');

        $this->post($url.'/complete', ['city_id' => 1, 'version' => $this->version($id)])->assertSessionHasErrors();
        self::assertSame('zero', $this->column($id, 'c_ApplicationState'));

        $references = [];
        foreach (['MP_FundType_ID', 'MP_PlaceType_ID'] as $key) {
            $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'microproject' && $r['child_columns'] === [$key]);
            $references[$key] = ApplicationFixture::row($relation['parent_source_table'], 1, ['c_ID' => 70]);
        }
        $this->post($url, ['city_id' => 1, 'version' => $this->version($id), 'references' => $references,
            'fields' => ['c_MP_RequestedAmount' => '1500.5'] + array_combine(array_map(fn ($n) => 'c_MP_Agreement'.$n, range(1, 5)), array_fill(0, 5, '1'))])->assertRedirect()->assertSessionHasNoErrors();
        $this->post($url.'/complete', ['city_id' => 1, 'version' => $this->version($id)])->assertSessionHasErrors('material');

        $this->post($url.'/details/material', ['city_id' => 1, 'version' => $this->version($id), 'fields' => ['c_Name' => 'ماكينة خياطة', 'c_Price' => '320', 'c_Amount' => '1']])->assertRedirect()->assertSessionHasNoErrors();
        $version = $this->version($id);
        $this->post($url.'/complete', ['city_id' => 1, 'version' => $version])->assertRedirect()->assertSessionHasNoErrors();
        $this->post($url.'/complete', ['city_id' => 1, 'version' => $version])->assertStatus(409);
        $this->post($url.'/complete', ['city_id' => 1, 'version' => $this->version($id)])->assertSessionHasErrors('version');
        self::assertSame('project', $this->column($id, 'c_ApplicationState'));
        self::assertSame(['On Hold', 'Waiting'], $this->states($id));
        self::assertSame(2, DB::table('op_task_microproject')->count());

        $person = $this->intake[1]['person'];
        $checklist = '/en/applications/'.$id.'/checklist/'.$person;
        $this->post($checklist, ['version' => $this->get($checklist)->viewData('version'), 'fields' => ['c_Notes' => 'لجنة التقييم وافقت', 'c_TeamDate' => '2026-10-25', 'c_CommityDate' => '2026-10-26'],
            'members' => ['c-new' => ['location' => 'c', 'member_id' => $this->members['c'], 'opinion' => '1']]])->assertRedirect()->assertSessionHasNoErrors();

        $this->transition($id, 'Accepted')->assertRedirect()->assertSessionHasNoErrors();
        $this->transition($id, 'Financed')->assertSessionHasErrors('state_id');
        self::assertSame(['On Hold', 'Waiting', 'Accepted'], $this->states($id));
        self::assertSame(4, DB::table('op_task_microproject')->count());
        $this->get($url)->assertOk()->assertSee('Accepted');
        $this->get('/ar/applications/'.$id)->assertOk()->assertSee('dir="rtl"', false)->assertSee('مقبول');
    }

    public function test_rejected_withdrawn_and_reopened_projects_keep_one_current_state_and_readable_history(): void
    {
        $id = $this->preliminary();
        $this->transition($id, 'Rejected', 'rejected note')->assertRedirect();
        $this->transition($id, 'Under Review')->assertRedirect();
        $this->transition($id, 'Withdrew', "  withdrawn \n")->assertRedirect();
        $this->transition($id, 'Withdrew')->assertSessionHasErrors('state_id');
        self::assertSame(['On Hold', 'Rejected', 'Under Review', 'Withdrew'], $this->states($id));
        self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
        $page = $this->get('/en/applications/'.$id)->assertOk();
        $page->assertSee('rejected note')->assertSee('  withdrawn ', false);
        self::assertSame(0, DB::table('op_task_microproject')->count());
    }

    public function test_double_submitted_transition_and_stale_form_create_one_event(): void
    {
        $id = $this->preliminary();
        $version = $this->version($id);
        $form = ['version' => $this->lifecycleVersion($id), 'state_id' => $this->intake[1]['states']['Delayed']];
        $this->post('/en/applications/'.$id.'/lifecycle', $form)->assertRedirect();
        $this->post('/en/applications/'.$id.'/lifecycle', $form)->assertStatus(409);
        self::assertSame(2, DB::table('op_microproject_state')->count());
        $this->post('/en/applications/'.$id, ['city_id' => 1, 'version' => $version, 'fields' => ['c_MP_Name' => 'late']])->assertRedirect();
        self::assertSame('late', $this->column($id, 'c_MP_Name'));
    }

    public function test_historical_project_without_current_flag_stays_readable_and_transitions_cleanly(): void
    {
        $id = $this->preliminary();
        DB::table('op_microproject_state')->delete();
        DB::table('operational_entities')->where('source_table_id', DB::table('op_microproject_state')->getConnection()->table('op_microproject')->where('id', $id)->value('source_table_id'))->where('id', $id)->update(['identity_kind' => 'legacy_primary']);
        $record = 100;
        $mp = DB::table('op_microproject')->where('id', $id)->first();
        DB::table('source_publications')->insert(['source_id' => 1, 'snapshot_id' => 10, 'publication_kind' => 'synthetic']);
        $this->legacy('op_microproject', $id, $record++);
        DB::table('op_microproject')->where('id', $id)->update(['c_MP_DateOfRequest' => '0000-00-00', 'c_MP_Note' => '  قديم  ']);
        // Two imported history rows, neither flagged current, one without a reason or note.
        $old = ApplicationFixture::row('microproject_state', 1, ['c_Date' => '2024-01-01', 'c_Current_State' => null], ['MicroProject_ID' => $id, 'State_ID' => $this->intake[1]['states']['Waiting']]);
        $new = ApplicationFixture::row('microproject_state', 1, ['c_Date' => '2025-02-02', 'c_Current_State' => null, 'c_Note' => ' سابق '], ['MicroProject_ID' => $id, 'State_ID' => $this->intake[1]['states']['Financed']]);
        $this->legacy('op_microproject_state', $old, $record++);
        $this->legacy('op_microproject_state', $new, $record++);

        $this->get('/en/applications/'.$id)->assertOk()->assertSee('Financed')->assertSee(' سابق ', false);
        self::assertSame($new, app(LifecycleService::class)->panel($this->account, $id)['current']->id);
        $this->transition($id, 'Finished')->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame('0000-00-00', $this->column($id, 'c_MP_DateOfRequest'));
        self::assertSame('  قديم  ', $this->column($id, 'c_MP_Note'));
        self::assertSame(1, DB::table('op_microproject_state')->where('c_Current_State', 1)->count());
        self::assertNull(DB::table('op_microproject_state')->where('id', $old)->value('c_Note'));
        self::assertSame(' سابق ', DB::table('op_microproject_state')->where('id', $new)->value('c_Note'));
    }

    public function test_every_new_route_requires_its_grants_and_writes_nothing_when_denied(): void
    {
        $id = $this->preliminary();
        $person = $this->intake[1]['person'];
        $routes = [
            ['get', '/en/applications/'.$id.'/checklist/'.$person, [], ['assessments.view', 'people.view', 'applications.view']],
            ['post', '/en/applications/'.$id.'/checklist/'.$person, ['version' => str_repeat('0', 64), 'fields' => ['c_Notes' => 'x']], ['assessments.manage']],
            ['post', '/en/applications/'.$id.'/lifecycle', ['version' => $this->lifecycleVersion($id), 'state_id' => $this->intake[1]['states']['Delayed']], ['lifecycle.manage', 'applications.view']],
            ['post', '/en/applications/'.$id.'/lifecycle/reason', ['version' => $this->lifecycleVersion($id), 'note' => 'x'], ['lifecycle.manage']],
        ];
        foreach ($routes as [$method, $uri, $data, $needed]) {
            foreach ($needed as $capability) {
                DB::table('account_city_capabilities')->update(['revoked_at' => null]);
                DB::table('account_city_capabilities')->where('capability', $capability)->update(['revoked_at' => now()]);
                $this->{$method}($uri, $data)->assertForbidden();
            }
        }
        DB::table('account_city_capabilities')->update(['revoked_at' => null]);
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        foreach ($routes as [$method, $uri, $data]) {
            $this->{$method}($uri, $data)->assertForbidden();
        }
        self::assertSame(1, DB::table('op_microproject_state')->count());
        self::assertSame(0, DB::table('op_checklist')->count());
    }

    public function test_a_project_of_another_city_is_not_reachable_through_lifecycle_or_checklist_routes(): void
    {
        $id = $this->preliminary();
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $own = app(ApplicationService::class)->create($this->account, 2, ['c_MP_Name' => 'City two', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[2]['person'], $this->intake[2]['references']);
        $this->get('/en/applications/'.$own)->assertOk();
        $this->get('/en/applications/'.$id)->assertForbidden();
        $this->post('/en/applications/'.$id.'/lifecycle', ['version' => str_repeat('0', 64), 'state_id' => $this->intake[2]['states']['Delayed']])->assertForbidden();
        $this->post('/en/applications/'.$own.'/lifecycle', ['version' => $this->lifecycleVersion($own), 'state_id' => $this->intake[1]['states']['Delayed']])->assertStatus(422);
        self::assertSame(1, DB::table('op_microproject_state')->where('city_id', 2)->count());
    }

    /** @return array<string> state names, oldest first */
    private function states(int $id): array
    {
        return app(LifecycleService::class)->panel($this->account, $id)['history']->sortBy('id')->pluck('state_name_en')->values()->all();
    }

    private function transition(int $id, string $state, ?string $note = null)
    {
        return $this->post('/en/applications/'.$id.'/lifecycle', ['version' => $this->lifecycleVersion($id), 'state_id' => $this->intake[1]['states'][$state], 'note' => $note]);
    }

    private function lifecycleVersion(int $id): string
    {
        return app(LifecycleService::class)->panel($this->account, $id)['version'];
    }

    private function version(int $id): string
    {
        return app(ApplicationService::class)->find($this->account, $id)['version'];
    }

    private function column(int $id, string $column): mixed
    {
        return DB::table('op_microproject')->where('id', $id)->value($column);
    }

    private function preliminary(): int
    {
        return app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'Synthetic', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake[1]['person'], $this->intake[1]['references']);
    }

    private function legacy(string $table, int $id, int $record): void
    {
        $tableId = DB::table($table)->where('id', $id)->value('source_table_id');
        DB::table('operational_entities')->where('id', $id)->update(['identity_kind' => 'legacy_primary']);
        DB::table('source_records')->insert(['id' => $record, 'source_id' => 1, 'table_id' => $tableId, 'snapshot_id' => 10]);
        DB::table('operational_origins')->insert(['source_record_id' => $record, 'source_id' => 1, 'source_table_id' => $tableId, 'snapshot_id' => 10, 'entity_id' => $id]);
    }
}
