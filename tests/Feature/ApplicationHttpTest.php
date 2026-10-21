<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Drafts\ApplicationDraftStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class ApplicationHttpTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = ApplicationFixture::intake(1);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
    }

    public function test_bilingual_create_and_same_session_retry_preserve_exact_input_once(): void
    {
        $this->get('/en/applications')->assertRedirect('/en/login');
        $this->actingAs($this->account);
        $this->get('/en/applications')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $form = $this->get('/ar/applications/create?city_id=1')->assertOk()->assertSee('dir="rtl"', false);
        $payload = $this->input() + ['create_token' => $form->viewData('createToken')];
        $this->post('/ar/applications', $payload)->assertRedirect();
        $this->post('/ar/applications', $payload)->assertRedirect();
        self::assertSame(1, DB::table('op_microproject')->count());
        self::assertSame(1001, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        self::assertSame('  exact note  ', DB::table('op_microproject')->value('c_MP_Note'));
        $id = DB::table('op_microproject')->value('id');
        $this->get('/en/applications/'.$id)->assertOk()->assertSee('  exact note  ', false);
        $this->get('/ar/applications/'.$id)->assertOk()->assertSee('dir="rtl"', false);
    }

    public function test_validation_recovery_preserves_exact_null_and_stale_version(): void
    {
        $id = $this->create();
        $version = app(ApplicationService::class)->find($this->account, $id)['version'];
        $url = '/en/applications/'.$id;
        $this->actingAs($this->account)->from($url)->post($url, [
            'city_id' => 1, '_form' => 'application', 'version' => $version,
            'fields' => ['c_MP_Name' => '', 'c_MP_Note' => '  recover me  '],
            'null_fields' => ['c_MP_RequestedAmount'],
        ])->assertRedirect($url)->assertSessionHasErrors()->assertSessionHasInput('version', $version)
            ->assertSessionHasInput('fields.c_MP_Note', '  recover me  ')->assertSessionHasInput('null_fields.0', 'c_MP_RequestedAmount');
        app(ApplicationService::class)->update($this->account, $id, ['c_MP_Name' => 'Newer'], $version);
        $this->get($url)->assertOk()->assertSee('name="version" value="'.$version.'"', false)->assertSee('  recover me  ', false);
        $this->post($url, ['city_id' => 1, 'version' => $version, 'fields' => ['c_MP_Name' => 'Stale']])->assertStatus(409);
        self::assertSame('Newer', DB::table('op_microproject')->where('id', $id)->value('c_MP_Name'));
    }

    public function test_draft_http_recovery_cannot_upgrade_stale_application_version(): void
    {
        $id = $this->create();
        $version = app(ApplicationService::class)->find($this->account, $id)['version'];
        $this->actingAs($this->account)->post(route('applications.drafts.save', ['locale' => 'en']), [
            'city_id' => 1, 'kind' => 'application', 'application_id' => $id, 'version' => $version,
            'fields' => ['c_MP_Name' => 'Recovered title', 'c_MP_Note' => '  recovered note  '], 'null_fields' => ['c_MP_RequestedAmount'],
        ])->assertRedirect();
        $draft = app(ApplicationDraftStore::class)->listing($this->account, 1)[0];
        app(ApplicationService::class)->update($this->account, $id, ['c_MP_Name' => 'Newer'], $version);
        $this->get(route('applications.drafts.restore', ['locale' => 'en', 'city_id' => 1, 'draft' => $draft['id']]))
            ->assertRedirect()->assertSessionHasInput('version', $version)->assertSessionHasInput('fields.c_MP_Note', '  recovered note  ')
            ->assertSessionHasInput('null_fields.0', 'c_MP_RequestedAmount');
        $this->get('/en/applications/'.$id)->assertOk()->assertSee('name="version" value="'.$version.'"', false);
        $this->post('/en/applications/'.$id, [
            'city_id' => 1, 'version' => $version, 'fields' => ['c_MP_Name' => 'Recovered title'],
        ])->assertStatus(409);
        $this->delete(route('applications.drafts.discard', ['locale' => 'en', 'draft' => $draft['id']]), [
            'city_id' => 1, 'draft_version' => $draft['version'],
        ])->assertRedirect();
        self::assertSame([], app(ApplicationDraftStore::class)->listing($this->account, 1));
        self::assertSame('Newer', DB::table('op_microproject')->where('id', $id)->value('c_MP_Name'));
    }

    public function test_revoked_management_blocks_writes_and_hides_edit_controls(): void
    {
        $id = $this->create();
        $version = app(ApplicationService::class)->find($this->account, $id)['version'];
        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->whereIn('capability', ['applications.manage', 'lifecycle.manage'])->update(['revoked_at' => now()]);
        $this->actingAs($this->account)->get('/en/applications/'.$id)->assertOk()->assertDontSee('name="version"', false);
        $this->post('/en/applications/'.$id, ['city_id' => 1, 'version' => $version, 'fields' => ['c_MP_Name' => 'Denied']])->assertForbidden();
        $this->get('/en/applications/create?city_id=1')->assertForbidden();
        $this->post(route('applications.drafts.save', ['locale' => 'en']), [
            'city_id' => 1, 'kind' => 'application', 'fields' => ['c_MP_Name' => 'Denied'],
        ])->assertForbidden();
    }

    public function test_existing_record_draft_requires_original_version(): void
    {
        $id = $this->create();
        $this->actingAs($this->account)->postJson(route('applications.drafts.save', ['locale' => 'en']), [
            'city_id' => 1, 'kind' => 'application', 'application_id' => $id, 'fields' => ['c_MP_Name' => 'No version'],
        ])->assertUnprocessable()->assertJsonValidationErrors('version');
        self::assertSame([], app(ApplicationDraftStore::class)->listing($this->account, 1));
    }

    public function test_repeated_child_posts_with_original_version_do_not_create_duplicates(): void
    {
        $id = $this->create();
        $this->actingAs($this->account);
        $version = app(ApplicationService::class)->find($this->account, $id)['version'];
        $payload = ['city_id' => 1, 'version' => $version, 'fields' => ['c_PropertyArea' => '100']];
        $this->post('/en/applications/'.$id.'/details/rehab', $payload)->assertRedirect();
        $this->post('/en/applications/'.$id.'/details/rehab', $payload)->assertStatus(409);
        self::assertSame(1, DB::table('op_microproject_rehab')->count());
        $person = ApplicationFixture::row('person', 1, ['c_P_FirstName' => 'Partner']);
        $version = app(ApplicationService::class)->find($this->account, $id)['version'];
        $payload = ['city_id' => 1, 'version' => $version, 'person_id' => $person, 'role' => 'partner', 'fields' => []];
        $this->post('/en/applications/'.$id.'/people', $payload)->assertRedirect();
        $this->post('/en/applications/'.$id.'/people', $payload)->assertStatus(409);
        self::assertSame(2, DB::table('op_person_microproject')->count());
    }

    private function input(): array
    {
        return ['city_id' => 1, 'beneficiary_id' => $this->intake['person'], 'references' => $this->intake['references'],
            'fields' => ['c_MP_Name' => 'طلب تجريبي', 'c_MP_DateOfRequest' => '2026-10-19', 'c_MP_Note' => '  exact note  ']];
    }

    private function create(): int
    {
        return app(ApplicationService::class)->create($this->account, 1, $this->input()['fields'], $this->intake['person'], $this->intake['references']);
    }
}
