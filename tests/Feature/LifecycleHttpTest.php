<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Lifecycle\LifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class LifecycleHttpTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $members;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = ApplicationFixture::intake(1);
        $this->members = ApplicationFixture::checklistCatalog(1);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->project = app(ApplicationService::class)->create($this->account, 1, ['c_MP_Name' => 'Synthetic', 'c_MP_DateOfRequest' => '2026-10-19'], $this->intake['person'], $this->intake['references']);
    }

    public function test_state_panel_and_change_render_in_both_languages_and_reject_stale_forms(): void
    {
        $this->actingAs($this->account);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('On Hold')->assertSee('Change state');
        $page = $this->get('/ar/applications/'.$this->project)->assertOk()->assertSee('dir="rtl"', false)->assertSee('تسجيل أولي');
        $version = app(LifecycleService::class)->panel($this->account, $this->project)['version'];
        $url = '/en/applications/'.$this->project.'/lifecycle';
        $this->post($url, ['version' => $version, 'state_id' => $this->intake['states']['On Hold']])->assertSessionHasErrors('state_id');
        $this->post($url, ['version' => $version, 'state_id' => $this->intake['states']['Delayed'], 'note' => '  exact  '])->assertRedirect();
        $this->post($url, ['version' => $version, 'state_id' => $this->intake['states']['Under Review']])->assertStatus(409);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Delayed')->assertSee('  exact  ', false);
        self::assertSame(2, DB::table('op_microproject_state')->count());
    }

    public function test_state_forms_are_hidden_and_refused_without_the_manage_grant(): void
    {
        DB::table('account_city_capabilities')->where('capability', 'lifecycle.manage')->update(['revoked_at' => now()]);
        $this->actingAs($this->account);
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('Change state');
        $version = app(LifecycleService::class)->panel($this->account, $this->project)['version'];
        $this->post('/en/applications/'.$this->project.'/lifecycle', ['version' => $version, 'state_id' => $this->intake['states']['Delayed']])->assertForbidden();
        self::assertSame(1, DB::table('op_microproject_state')->count());
    }

    public function test_checklist_page_saves_and_re_renders_exact_values_in_both_languages(): void
    {
        $this->actingAs($this->account);
        $url = '/en/applications/'.$this->project.'/checklist/'.$this->intake['person'];
        $this->get($url)->assertOk()->assertSee('Assessment checklist');
        $this->get('/ar/applications/'.$this->project.'/checklist/'.$this->intake['person'])->assertOk()->assertSee('dir="rtl"', false);
        $version = $this->get($url)->viewData('version');
        $this->post($url, [
            'version' => $version,
            'fields' => ['c_Notes' => "  exact  \n", 'c_HasHomeVisit' => '1', 'c_HVisit_Date' => '', 'c_TeamDate' => '2026-10-21'],
            'references' => ['HVisit_Visitor1_ID' => $this->members['t']],
            'members' => ['k-new' => ['id' => '', 'location' => 'k', 'member_id' => ''], 't-new' => ['id' => '', 'location' => 't', 'member_id' => $this->members['t'], 'opinion' => '1', 'notes' => 'ok']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame("  exact  \n", DB::table('op_checklist')->value('c_Notes'));
        self::assertNull(DB::table('op_checklist')->value('c_HVisit_Date'));
        self::assertSame(1, DB::table('op_person_checklistmembers')->count());
        $this->get($url)->assertOk()->assertSee('Synthetic t');
        // The form resubmits stored values; they are untouched and must not trip validation.
        DB::table('op_checklist')->update(['c_ProTrain_BDate' => '0000-00-00']);
        $this->post($url, ['version' => $this->get($url)->viewData('version'), 'fields' => ['c_ProTrain_BDate' => '0000-00-00', 'c_VisitsReport' => 'r']])->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame('0000-00-00', DB::table('op_checklist')->value('c_ProTrain_BDate'));
    }

    public function test_checklist_routes_refuse_missing_grants_and_strangers(): void
    {
        $this->actingAs($this->account);
        $stranger = ApplicationFixture::row('person', 1, ['c_P_FirstName' => 'Other']);
        $this->get('/en/applications/'.$this->project.'/checklist/'.$stranger)->assertNotFound();
        DB::table('account_city_capabilities')->where('capability', 'assessments.view')->update(['revoked_at' => now()]);
        $this->get('/en/applications/'.$this->project.'/checklist/'.$this->intake['person'])->assertForbidden();
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('Assessment checklist');
    }
}
