<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\AccountPasswords;
use App\Services\AuthenticateAccount;
use App\Services\Staff\AccountAdminService;
use App\Support\SyntheticFoundationFixture;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class AccountAdministrationTest extends TestCase
{
    private WebAccount $admin;

    private AccountAdminService $service;

    /** @var array<string, int> source_records ids of the invented historical users */
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->admin = ApplicationFixture::account();
        $this->service = app(AccountAdminService::class);
        // The administrator holds `accounts.manage` and the data-entry capabilities in Aleppo only.
        DB::table('account_city_capabilities')->where('account_id', $this->admin->id)->where('city_id', 2)->delete();
        StaffFixture::grant($this->admin, [1], ['accounts.manage', 'notifications.view', ...AccessRoles::capabilities('data-entry')]);
        $this->users = [
            'dup1' => StaffFixture::user(1, 101, 'dup.user'), 'solo' => StaffFixture::user(1, 102, 'solo.user'), 'away' => StaffFixture::user(1, 103, 'away.user', ['UserRoleID' => 6]),
            'fin' => StaffFixture::user(1, 104, 'fin.user', ['UserRoleID' => 3, 'IsCashier' => 1]), 'dup2' => StaffFixture::user(2, 201, 'dup.user'), 'other2' => StaffFixture::user(2, 202, 'other.two'),
        ];
    }

    private function account(string $name, array $grants = []): WebAccount
    {
        $id = DB::table('web_accounts')->insertGetId(['username' => $name, 'password_hash' => 'x', 'password_changed_at' => now('UTC'), 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        foreach ($grants as $city => $capabilities) {
            StaffFixture::grant(WebAccount::findOrFail($id), [$city], $capabilities);
        }

        return WebAccount::findOrFail($id);
    }

    private function manageBoth(): void
    {
        StaffFixture::grant($this->admin, [2], ['accounts.manage', ...AccessRoles::capabilities('data-entry')]);
    }

    private function names(array $filters = []): array
    {
        return $this->service->index($this->admin, $filters)['rows']->pluck('username')->all();
    }

    private function expectStatus(int $status, callable $call): void
    {
        try {
            $call();
            self::fail('Expected HTTP '.$status);
        } catch (HttpException $e) {
            self::assertSame($status, $e->getStatusCode());
        }
    }

    private function invalid(string $field, callable $call): void
    {
        try {
            $call();
            self::fail('Expected a validation error for '.$field);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());
        }
    }

    private function active(WebAccount $account, int $city): array
    {
        $caps = DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', $city)->whereNull('revoked_at')->pluck('capability')->all();
        sort($caps);

        return $caps;
    }

    public function test_an_administrator_sees_accounts_of_its_cities_and_unassigned_ones_only(): void
    {
        $this->account('aleppo.staff', [1 => ['people.view']]);
        $this->account('damascus.staff', [2 => ['people.view']]);
        $this->account('nobody');
        $revoked = $this->account('was.in.aleppo', [1 => ['people.view']]);
        DB::table('account_city_capabilities')->where('account_id', $revoked->id)->update(['revoked_at' => now()]);

        self::assertEqualsCanonicalizing(['synthetic.applications', 'aleppo.staff', 'nobody', 'was.in.aleppo'], $this->names(), 'Damascus-only staff is invisible; accounts without any city are listed so they can be assigned');
        self::assertSame(['aleppo.staff'], $this->names(['q' => 'staff', 'city' => 1]));
        self::assertSame(['aleppo.staff', 'was.in.aleppo'], $this->names(['q' => 'aleppo']));
        self::assertEqualsCanonicalizing($this->names(), $this->names(['city' => 2]), 'a city the administrator does not manage is ignored as a filter');
        $this->expectStatus(404, fn () => $this->service->show($this->admin, (int) DB::table('web_accounts')->where('username', 'damascus.staff')->value('id')));

        DB::table('web_accounts')->where('username', 'aleppo.staff')->update(['disabled_at' => now()]);
        self::assertSame(['aleppo.staff'], $this->names(['status' => 'disabled']));
        self::assertNotContains('aleppo.staff', $this->names(['status' => 'active']));
        $row = $this->service->index($this->admin, ['q' => 'synthetic'])['rows']->first();
        self::assertTrue($row->self);
        self::assertSame(DB::table('account_city_capabilities')->where('account_id', $this->admin->id)->where('city_id', 1)->whereNull('revoked_at')->count(), $row->grants[1]);

        $plain = $this->account('plain.user', [1 => ['people.view']]);
        $this->expectStatus(403, fn () => $this->service->index($plain, []));
    }

    public function test_capabilities_are_assigned_per_city_within_what_the_administrator_holds(): void
    {
        $this->manageBoth();
        $staff = $this->account('aleppo.staff', [1 => ['people.view', 'finance.view']]);
        StaffFixture::grant($staff, [2], ['people.view']);
        $result = $this->service->saveCapabilities($this->admin, $staff->id, 1, ['people.view', 'people.manage', 'special_cases.view']);
        self::assertSame(['granted' => 2, 'revoked' => 0], $result, 'finance.view is not held by the administrator, so it is left alone');
        self::assertSame(['finance.view', 'people.manage', 'people.view', 'special_cases.view'], $this->active($staff, 1));
        self::assertSame(['people.view'], $this->active($staff, 2), 'another city is untouched');

        $result = $this->service->saveCapabilities($this->admin, $staff->id, 1, ['people.view']);
        self::assertSame(['granted' => 0, 'revoked' => 2], $result);
        self::assertSame(['finance.view', 'people.view'], $this->active($staff, 1));
        $this->service->saveCapabilities($this->admin, $staff->id, 2, ['people.view', 'visits.view']);
        self::assertSame(['people.view', 'visits.view'], $this->active($staff, 2), 'independent per city');

        $events = DB::table('account_audit_events')->whereIn('event_type', ['capability.granted', 'capability.revoked'])->where('actor_account_id', $this->admin->id)->get();
        self::assertGreaterThanOrEqual(5, $events->count(), 'every change is audited with the acting account');
        self::assertNotEmpty($events->first()->decision_locator);

        $this->invalid('capabilities', fn () => $this->service->saveCapabilities($this->admin, $staff->id, 1, ['finance.manage']));
        $this->invalid('capabilities', fn () => $this->service->saveCapabilities($this->admin, $staff->id, 1, ['not.a.capability']));
        self::assertSame(['finance.view', 'people.view'], $this->active($staff, 1));

        // Granting the right to administer is possible only because the administrator holds it itself.
        $this->service->saveCapabilities($this->admin, $staff->id, 1, ['people.view', 'accounts.manage']);
        self::assertContains('accounts.manage', $this->active($staff, 1));
    }

    public function test_administrators_cannot_act_outside_their_cities_or_on_themselves(): void
    {
        $staff = $this->account('damascus.staff', [2 => ['people.view']]);
        $this->expectStatus(403, fn () => $this->service->saveCapabilities($this->admin, $staff->id, 2, ['people.view']));
        $this->expectStatus(404, fn () => $this->service->saveCapabilities($this->admin, $staff->id, 1, ['people.view']), 'an account of other cities is not visible');
        $mine = $this->account('aleppo.staff', [1 => ['people.view']]);
        $this->expectStatus(403, fn () => $this->service->saveCapabilities($this->admin, $this->admin->id, 1, []), 'no editing of own grants');
        $this->expectStatus(403, fn () => $this->service->assignRole($this->admin, $this->admin->id, 1, 'data-entry'));
        $this->expectStatus(403, fn () => $this->service->saveCapabilities($mine, $mine->id, 1, []));
        self::assertGreaterThanOrEqual(3, DB::table('account_audit_events')->where('event_type', 'access.denied')->count());
    }

    public function test_a_role_is_recorded_and_granted_only_when_the_administrator_holds_all_of_it_and_can_be_removed(): void
    {
        $staff = $this->account('aleppo.staff', [1 => ['finance.view']]);
        self::assertSame(count(AccessRoles::capabilities('data-entry')), $this->service->assignRole($this->admin, $staff->id, 1, 'data-entry'));
        self::assertSame(count(AccessRoles::capabilities('data-entry')) + 1, count($this->active($staff, 1)));
        self::assertSame(['data-entry'], DB::table('account_city_roles')->where('account_id', $staff->id)->where('city_id', 1)->whereNull('revoked_at')->pluck('role')->all(), 'the assignment itself is recorded');
        $this->invalid('role', fn () => $this->service->assignRole($this->admin, $staff->id, 1, 'unknown'));
        $this->invalid('role', fn () => $this->service->assignRole($this->admin, $staff->id, 1, 'financial'), 'the administrator does not hold finance.manage');
        $panel = collect($this->service->show($this->admin, $staff->id)['panels'])->first();
        self::assertNotContains('financial', $panel['roles']);
        self::assertContains('data-entry', $panel['roles']);
        self::assertSame(['data-entry'], $panel['held']);

        // A second role that shares capabilities keeps them when the first is removed.
        $this->service->assignRole($this->admin, $staff->id, 1, 'guest');
        $revoked = $this->service->revokeRole($this->admin, $staff->id, 1, 'data-entry');
        self::assertSame(count(AccessRoles::capabilities('data-entry')) - count(AccessRoles::capabilities('guest')), $revoked);
        self::assertSame(['applications.view', 'finance.view', 'people.view', 'projects.view'], $this->active($staff, 1), 'the guest capabilities and the unrelated one stay');
        self::assertSame(['guest'], $this->service->show($this->admin, $staff->id)['panels'][0]['held']);
        $this->invalid('role', fn () => $this->service->revokeRole($this->admin, $staff->id, 1, 'data-entry'));
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'accounts.role_assigned')->count());
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'accounts.role_revoked')->count());

        DB::table('account_city_capabilities')->where('account_id', $this->admin->id)->where('capability', 'visits.delete')->update(['revoked_at' => now()]);
        $this->invalid('role', fn () => $this->service->assignRole($this->admin, $staff->id, 1, 'data-entry'));
        self::assertNotContains('data-entry', collect($this->service->show($this->admin, $staff->id)['panels'])->first()['roles'], 'the role is no longer offered');
    }

    public function test_identities_are_linked_and_revoked_after_review_and_only_in_managed_cities(): void
    {
        $staff = $this->account('aleppo.staff', [1 => ['people.view']]);
        $this->service->link($this->admin, $staff->id, 1, $this->users['dup1']);
        $link = DB::table('account_legacy_links')->where('account_id', $staff->id)->first();
        self::assertSame(1, (int) $link->source_id);
        self::assertSame(1, DB::table('web_principals')->where('source_record_id', $this->users['dup1'])->count(), 'the identity pointer is created on first use');
        self::assertNull($link->revoked_at);
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'legacy.linked')->where('actor_account_id', $this->admin->id)->count());

        $other = $this->account('another.staff', [1 => ['people.view']]);
        $this->invalid('identity', fn () => $this->service->link($this->admin, $other->id, 1, $this->users['dup1']));
        $this->invalid('identity', fn () => $this->service->link($this->admin, $other->id, 1, $this->users['away']));
        $this->invalid('identity', fn () => $this->service->link($this->admin, $other->id, 1, 999999));
        $this->expectStatus(403, fn () => $this->service->link($this->admin, $other->id, 2, $this->users['dup2']));
        self::assertSame(0, DB::table('account_legacy_links')->where('account_id', $other->id)->count());

        $this->service->setLinkRevoked($this->admin, $staff->id, (int) $link->id, true);
        self::assertNotNull(DB::table('account_legacy_links')->where('id', $link->id)->value('revoked_at'));
        $this->service->setLinkRevoked($this->admin, $staff->id, (int) $link->id, false);
        self::assertNull(DB::table('account_legacy_links')->where('id', $link->id)->value('revoked_at'));
        $this->expectStatus(404, fn () => $this->service->setLinkRevoked($this->admin, $other->id, (int) $link->id, true));

        $detail = $this->service->show($this->admin, $staff->id);
        self::assertSame(['dup.user'], $detail['links']->pluck('username')->all());
        self::assertNotContains($this->users['dup1'], $detail['candidates']->pluck('record')->all(), 'a linked identity is no longer a candidate');
        self::assertSame(['fin.user', 'solo.user'], $detail['candidates']->pluck('username')->all(), 'only unlinked, in-service identities of managed cities');
    }

    public function test_an_account_that_borrows_its_password_cannot_lose_that_identity(): void
    {
        $this->manageBoth();
        $id = $this->service->create($this->admin, ['identities' => ['1:'.$this->users['dup1']], 'credential' => '1:'.$this->users['dup1'], 'confirm' => '1']);
        $link = DB::table('account_legacy_links')->where('account_id', $id)->first();
        $this->invalid('link', fn () => $this->service->setLinkRevoked($this->admin, $id, (int) $link->id, true));
        DB::table('web_accounts')->where('id', $id)->update(['password_hash' => 'modern']);
        $this->service->setLinkRevoked($this->admin, $id, (int) $link->id, true);
        self::assertNotNull(DB::table('account_legacy_links')->where('id', $link->id)->value('revoked_at'));
    }

    public function test_accounts_are_created_from_reviewed_duplicate_identities_and_can_log_in_with_the_desktop_password(): void
    {
        $this->manageBoth();
        $groups = $this->service->candidates($this->admin);
        $duplicates = $groups->firstWhere('username', 'dup.user');
        self::assertCount(2, $duplicates->identities, 'the same name in two cities is one group for review');
        self::assertNull($groups->firstWhere('username', 'away.user'), 'out-of-service identities are not offered');

        DB::table('legacy_d_user')->where('record_id', $this->users['dup2'])->update(['Email' => ' Dup.User@Example.ORG ']);
        $input = ['identities' => ['1:'.$this->users['dup1'], '2:'.$this->users['dup2']], 'credential' => '2:'.$this->users['dup2'], 'confirm' => '1', 'roles' => [1 => 'data-entry']];
        $id = $this->service->create($this->admin, $input);
        $account = WebAccount::findOrFail($id);
        self::assertSame('dup.user', $account->username);
        self::assertSame(2, DB::table('account_legacy_links')->where('account_id', $id)->count());
        self::assertSame(count(AccessRoles::capabilities('data-entry')), count($this->active($account, 1)));
        self::assertSame([], $this->active($account, 2), 'no role was chosen for the second city');
        self::assertSame(['data-entry'], DB::table('account_city_roles')->where('account_id', $id)->whereNull('revoked_at')->pluck('role')->all(), 'the role is recorded, in the first city only');
        self::assertSame('dup.user@example.org', DB::table('web_accounts')->where('id', $id)->value('email'), 'the e-mail comes from the password identity, normalised');
        self::assertSame(2, (int) DB::table('web_principals')->where('id', $account->credential_principal_id)->value('source_id'));
        $provisioned = DB::table('account_audit_events')->where('event_type', 'account.provisioned')->first();
        self::assertSame($this->admin->id, (int) $provisioned->actor_account_id);
        self::assertSame($id, (int) $provisioned->subject_account_id);

        $logged = app(AuthenticateAccount::class)->attempt('dup.user', SyntheticFoundationFixture::PASSWORD);
        self::assertSame($id, $logged?->id, 'the account inherits the reviewed identity\'s desktop password');
        self::assertNull(app(AuthenticateAccount::class)->attempt('dup.user', 'wrong password'));

        self::assertNull($this->service->candidates($this->admin)->firstWhere('username', 'dup.user'), 'linked identities leave the candidate list');
        $this->invalid('identities', fn () => $this->service->create($this->admin, $input));
    }

    public function test_creation_is_refused_for_bad_input_or_cities_the_administrator_does_not_manage(): void
    {
        $base = ['identities' => ['1:'.$this->users['solo']], 'credential' => '1:'.$this->users['solo'], 'confirm' => '1'];
        $this->invalid('confirm', fn () => $this->service->create($this->admin, ['confirm' => null] + $base));
        $this->invalid('identities', fn () => $this->service->create($this->admin, ['identities' => []] + $base));
        $this->invalid('identities', fn () => $this->service->create($this->admin, ['identities' => ['x:y']] + $base));
        $this->invalid('identities', fn () => $this->service->create($this->admin, ['identities' => ['1:'.$this->users['away']], 'credential' => '1:'.$this->users['away']] + $base));
        $this->invalid('credential', fn () => $this->service->create($this->admin, ['credential' => '1:'.$this->users['fin']] + $base));
        $this->invalid('username', fn () => $this->service->create($this->admin, ['username' => ' padded '] + $base));
        $this->invalid('username', fn () => $this->service->create($this->admin, ['username' => str_repeat('x', 201)] + $base));
        $this->invalid('username', fn () => $this->service->create($this->admin, ['username' => 'synthetic.applications'] + $base));
        $this->invalid('roles', fn () => $this->service->create($this->admin, ['roles' => [1 => 'nonsense']] + $base));
        $this->expectStatus(403, fn () => $this->service->create($this->admin, ['identities' => ['2:'.$this->users['dup2']], 'credential' => '2:'.$this->users['dup2']] + $base));
        self::assertSame(0, DB::table('account_legacy_links')->count());

        DB::table('account_city_capabilities')->where('account_id', $this->admin->id)->where('capability', 'people.manage')->update(['revoked_at' => now()]);
        $this->invalid('roles', fn () => $this->service->create($this->admin, ['roles' => [1 => 'data-entry']] + $base));
        $id = $this->service->create($this->admin, ['username' => 'Custom.Name'] + $base);
        self::assertSame('Custom.Name', WebAccount::findOrFail($id)->username);
    }

    public function test_disabling_needs_every_city_the_account_is_active_in_and_never_ones_own_account(): void
    {
        $staff = $this->account('aleppo.staff', [1 => ['people.view']]);
        $both = $this->account('both.cities', [1 => ['people.view'], 2 => ['people.view']]);
        $this->service->setDisabled($this->admin, $staff->id, true);
        self::assertNotNull(DB::table('web_accounts')->where('id', $staff->id)->value('disabled_at'));
        self::assertFalse(app(AccountAccess::class)->active($staff), 'a disabled account loses every grant at once');
        self::assertFalse(app(AccountAccess::class)->check($staff, 1, 'people.view'));
        $this->service->setDisabled($this->admin, $staff->id, false);
        self::assertTrue(app(AccountAccess::class)->active($staff));
        self::assertSame(['accounts.disabled', 'accounts.enabled'], DB::table('account_audit_events')->whereIn('event_type', ['accounts.disabled', 'accounts.enabled'])->orderBy('id')->pluck('event_type')->all());

        $this->expectStatus(403, fn () => $this->service->setDisabled($this->admin, $both->id, true));
        self::assertFalse($this->service->show($this->admin, $both->id)['canDisable']);
        $this->expectStatus(403, fn () => $this->service->setDisabled($this->admin, $this->admin->id, true));
        $this->manageBoth();
        $this->service->setDisabled($this->admin, $both->id, true);
        self::assertNotNull(DB::table('web_accounts')->where('id', $both->id)->value('disabled_at'));
    }

    public function test_the_staff_directory_lists_the_accounts_of_managed_cities_with_roles_and_tasks(): void
    {
        $finance = StaffFixture::account('finance.person', [1 => ['financial']], 'finance@example.org');
        StaffFixture::grant($finance, [1], ['tasks.manage']);
        StaffFixture::account('damascus.person', [2 => ['legal']]);
        $task = StaffFixture::task(1, 3, 'Story');
        StaffFixture::responsible($finance, 1, $task);
        $directory = $this->service->staff($this->admin, []);
        self::assertCount(1, $directory['groups']);
        $users = $directory['groups'][0]['users']->keyBy('username');
        self::assertSame(['financial'], $users['finance.person']->roles);
        self::assertSame(1, $users['finance.person']->tasks);
        self::assertSame('finance@example.org', $users['finance.person']->email);
        self::assertArrayNotHasKey('damascus.person', $users->all(), 'Damascus staff is not shown');
        self::assertSame(1, count($this->service->staff($this->admin, ['city' => 2])['groups']), 'a city the administrator does not manage is ignored');
    }

    public function test_pages_and_posts_work_over_http_in_both_languages(): void
    {
        $this->manageBoth();
        $staff = $this->account('aleppo.staff', [1 => ['people.view']]);
        $this->actingAs($this->admin);
        $this->get('/en/admin/accounts')->assertOk()->assertSee('Staff accounts')->assertSee('aleppo.staff')->assertSee('New account');
        $this->get('/ar/admin/accounts?status=disabled')->assertOk()->assertSee('dir="rtl"', false)->assertSee('حسابات الموظفين');
        $this->get('/en/admin/accounts?status=x')->assertSessionHasErrors('status');
        $this->get('/en/admin/accounts/'.$staff->id)->assertOk()->assertSee('aleppo.staff')->assertSee('View people')->assertSee('Apply role')->assertSee('Disable account');
        $this->get('/ar/admin/accounts/'.$staff->id)->assertOk()->assertSee('عرض الأشخاص');
        $this->get('/en/admin/accounts/'.$this->admin->id)->assertOk()->assertSee('your own account');
        $this->get('/en/admin/accounts/999999')->assertNotFound();

        $this->post('/en/admin/accounts/'.$staff->id.'/capabilities', ['city' => 1, 'capabilities' => ['people.view', 'visits.view']])->assertRedirect('/en/admin/accounts/'.$staff->id);
        self::assertSame(['people.view', 'visits.view'], $this->active($staff, 1));
        $this->post('/en/admin/accounts/'.$staff->id.'/capabilities', ['city' => 1, 'capabilities' => ['bogus']])->assertSessionHasErrors('capabilities.0');
        $this->post('/en/admin/accounts/'.$staff->id.'/role', ['city' => 1, 'role' => 'data-entry'])->assertRedirect();
        $this->post('/en/admin/accounts/'.$staff->id.'/links', ['identity' => '1:'.$this->users['solo']])->assertRedirect();
        $link = (int) DB::table('account_legacy_links')->where('account_id', $staff->id)->value('id');
        $this->post("/en/admin/accounts/{$staff->id}/links/{$link}", ['revoked' => '1'])->assertRedirect();
        $this->post("/en/admin/accounts/{$staff->id}/disable", ['disabled' => '1'])->assertRedirect();
        self::assertNotNull(DB::table('web_accounts')->where('id', $staff->id)->value('disabled_at'));
        $this->post('/en/admin/accounts/'.$staff->id.'/links', ['identity' => 'nonsense'])->assertSessionHasErrors('identity');

        $this->get('/en/admin/accounts/new')->assertOk()->assertSee('dup.user')->assertSee('Same name in several cities');
        $this->post('/en/admin/accounts', ['identities' => ['1:'.$this->users['dup1'], '2:'.$this->users['dup2']], 'credential' => '1:'.$this->users['dup1'], 'confirm' => '1'])->assertRedirect();
        self::assertSame(1, DB::table('web_accounts')->where('username', 'dup.user')->count());
        $this->post('/en/admin/accounts', ['identities' => ['1:'.$this->users['solo']], 'credential' => '1:'.$this->users['solo']])->assertSessionHasErrors('confirm');
        $this->get('/en/admin/staff')->assertOk()->assertSee('Staff directory')->assertSee('aleppo.staff');

        // Roles, e-mail and tasks.
        $this->get('/en/admin/accounts/'.$staff->id)->assertOk()->assertSee('Roles in this city')->assertSee('Data entry');
        $this->post('/en/admin/accounts/'.$staff->id.'/role/revoke', ['city' => 1, 'role' => 'data-entry'])->assertRedirect();
        self::assertSame([], DB::table('account_city_roles')->where('account_id', $staff->id)->whereNull('revoked_at')->pluck('role')->all());
        $this->post('/en/admin/accounts/'.$staff->id.'/role/revoke', ['city' => 1, 'role' => 'data-entry'])->assertSessionHasErrors('role');
        $this->post('/en/admin/accounts/'.$staff->id.'/email', ['email' => 'Staff@Example.org'])->assertRedirect();
        self::assertSame('staff@example.org', DB::table('web_accounts')->where('id', $staff->id)->value('email'));
        $this->post('/en/admin/accounts/'.$staff->id.'/email', ['email' => 'bad'])->assertSessionHasErrors('email');
        $task = StaffFixture::task(1, 3, 'Story');
        $worker = StaffFixture::account('task.worker');
        StaffFixture::grant($worker, [1], ['tasks.manage']);
        $this->get('/en/admin/tasks')->assertOk()->assertSee('Task responsibilities')->assertSee('Story');
        $this->get('/ar/admin/tasks?city=1')->assertOk()->assertSee('مسؤوليات المهام');
        $this->post('/en/admin/tasks', ['city' => 1, 'assign' => [$task => [$worker->id]]])->assertRedirect('/en/admin/tasks?city=1');
        self::assertSame(1, DB::table('account_city_tasks')->where('account_id', $worker->id)->whereNull('revoked_at')->count());
        $this->post('/en/admin/tasks', ['city' => 1, 'assign' => [$task => ['x']]])->assertSessionHasErrors();
        $this->post('/en/admin/accounts/plain', ['username' => 'plain.new', 'email' => 'plain@example.org', 'roles' => [1 => 'data-entry']])->assertRedirect()->assertSessionHas('one_time_password');
        $this->get('/en/admin/accounts/new')->assertOk()->assertSee('New account without a desktop identity');
        $this->post('/en/admin/accounts/plain', ['username' => 'plain.bad', 'email' => 'x', 'roles' => [1 => 'data-entry']])->assertSessionHasErrors('email');
        $this->get('/ar/admin/staff?city=1')->assertOk()->assertSee('دليل الموظفين');

        $plain = $this->account('plain.user', [1 => ['people.view', 'applications.view']]);
        $this->actingAs($plain);
        $this->get('/en/admin/accounts')->assertForbidden();
        $this->post('/en/admin/accounts/'.$staff->id.'/disable', ['disabled' => '0'])->assertForbidden();
        $this->get('/en/applications')->assertOk()->assertDontSee('>Accounts<', false);
    }

    public function test_an_account_without_a_desktop_identity_gets_a_one_time_password_and_must_replace_it(): void
    {
        $result = $this->service->createPlain($this->admin, ['username' => 'new.staff', 'email' => ' New.Staff@Example.org ', 'roles' => [1 => 'data-entry', 2 => '']]);
        $account = WebAccount::findOrFail($result['id']);
        self::assertSame('new.staff@example.org', $account->email);
        self::assertNull($account->credential_principal_id);
        self::assertNull($account->password_changed_at);
        self::assertNotSame($result['password'], $account->password_hash, 'only the hash is stored');
        self::assertSame(count(AccessRoles::capabilities('data-entry')), count($this->active($account, 1)));
        self::assertSame($account->id, app(AuthenticateAccount::class)->attempt('new.staff', $result['password'])?->id);
        self::assertTrue(app(AccountPasswords::class)->mustChange($account));
        $provisioned = DB::table('account_audit_events')->where('event_type', 'account.provisioned')->first();
        self::assertSame($this->admin->id, (int) $provisioned->actor_account_id);

        $base = ['username' => 'second.staff', 'email' => 'second@example.org', 'roles' => [1 => 'data-entry']];
        $this->invalid('username', fn () => $this->service->createPlain($this->admin, ['username' => 'new.staff'] + $base));
        $this->invalid('username', fn () => $this->service->createPlain($this->admin, ['username' => ''] + $base));
        $this->invalid('email', fn () => $this->service->createPlain($this->admin, ['email' => 'not an e-mail'] + $base));
        $this->invalid('email', fn () => $this->service->createPlain($this->admin, ['email' => 'NEW.STAFF@example.org'] + $base));
        $this->invalid('roles', fn () => $this->service->createPlain($this->admin, ['roles' => [1 => '']] + $base));
        $this->invalid('roles', fn () => $this->service->createPlain($this->admin, ['roles' => [1 => 'financial']] + $base));
        $this->expectStatus(403, fn () => $this->service->createPlain($this->admin, ['roles' => [2 => 'data-entry']] + $base));
        self::assertSame(1, DB::table('web_accounts')->where('username', 'like', '%.staff')->count(), 'refused attempts create nothing');
    }

    public function test_an_administrator_sets_the_e_mail_only_of_accounts_it_cannot_be_outranked_by(): void
    {
        $staff = $this->account('aleppo.staff', [1 => ['people.view']]);
        $this->service->setEmail($this->admin, $staff->id, ' Staff@Example.org ');
        self::assertSame('staff@example.org', DB::table('web_accounts')->where('id', $staff->id)->value('email'));
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'accounts.email_set')->count());
        $this->service->setEmail($this->admin, $staff->id, '');
        self::assertNull(DB::table('web_accounts')->where('id', $staff->id)->value('email'));
        $this->invalid('email', fn () => $this->service->setEmail($this->admin, $staff->id, 'nope'));
        $other = $this->account('other.staff', [1 => ['people.view']]);
        $this->service->setEmail($this->admin, $other->id, 'taken@example.org');
        $this->invalid('email', fn () => $this->service->setEmail($this->admin, $staff->id, 'taken@example.org'));

        $stronger = $this->account('finance.chief', [1 => ['people.view', 'finance.manage']]);
        $this->expectStatus(403, fn () => $this->service->setEmail($this->admin, $stronger->id, 'chief@example.org'));
        self::assertFalse($this->service->show($this->admin, $stronger->id)['canEmail']);
        $this->expectStatus(403, fn () => $this->service->setEmail($this->admin, $this->admin->id, 'me@example.org'));
    }

    public function test_the_task_matrix_assigns_tasks_per_city_to_accounts_that_may_manage_tasks(): void
    {
        $this->manageBoth();
        $tasks = [StaffFixture::task(1, 3, 'Story'), StaffFixture::task(1, 16, 'Purchases'), StaffFixture::task(1, 39, 'Denomination', 'a'), StaffFixture::task(1, 999, 'Not on the board')];
        $one = StaffFixture::account('task.one');
        $two = StaffFixture::account('task.two');
        $nobody = StaffFixture::account('cannot.manage');
        StaffFixture::grant($one, [1], ['tasks.manage']);
        StaffFixture::grant($two, [1, 2], ['tasks.manage']);
        StaffFixture::grant($nobody, [1], ['tasks.view']);

        $matrix = $this->service->taskMatrix($this->admin, 1);
        self::assertSame(['Denomination', 'Story', 'Purchases'], array_map(fn ($task) => $task->name, $matrix['tasks']), 'only the board\'s tasks, applications first');
        self::assertEqualsCanonicalizing(['task.one', 'task.two'], array_values(array_filter($matrix['accounts']->pluck('username')->all(), fn ($n) => str_starts_with($n, 'task.'))), 'only accounts that can manage tasks are offered');
        self::assertNotContains('cannot.manage', $matrix['accounts']->pluck('username')->all());

        self::assertSame(['added' => 3, 'withdrawn' => 0], $this->service->saveTasks($this->admin, 1, [$tasks[0] => [$one->id, $two->id], $tasks[1] => [$one->id]]));
        $active = fn () => DB::table('account_city_tasks')->whereNull('revoked_at')->orderBy('task_id')->orderBy('account_id')->get(['task_id', 'account_id'])->map(fn ($r) => [(int) $r->task_id, (int) $r->account_id])->all();
        self::assertSame([[$tasks[0], $one->id], [$tasks[0], $two->id], [$tasks[1], $one->id]], $active());
        self::assertSame(['added' => 1, 'withdrawn' => 2], $this->service->saveTasks($this->admin, 1, [$tasks[0] => [$two->id], $tasks[2] => [$two->id]]));
        self::assertSame([[$tasks[0], $two->id], [$tasks[2], $two->id]], $active());
        self::assertSame(['added' => 1, 'withdrawn' => 0], $this->service->saveTasks($this->admin, 1, [$tasks[0] => [$two->id, $one->id], $tasks[2] => [$two->id]]), 'a withdrawn assignment is reactivated, not duplicated');
        self::assertSame(5 - 1, DB::table('account_city_tasks')->count());
        self::assertSame($tasks[0], (int) DB::table('account_city_tasks')->where('account_id', $one->id)->where('task_id', $tasks[0])->value('task_id'));

        $this->invalid('tasks', fn () => $this->service->saveTasks($this->admin, 1, [$tasks[3] => [$one->id]]));
        $this->invalid('tasks', fn () => $this->service->saveTasks($this->admin, 1, [$tasks[0] => [$nobody->id]]));
        $this->invalid('tasks', fn () => $this->service->saveTasks($this->admin, 1, [999999 => [$one->id]]));
        $this->expectStatus(403, fn () => $this->service->saveTasks($this->admin, 3, []));
        $this->expectStatus(403, fn () => $this->service->taskMatrix($this->admin, 3));
        self::assertGreaterThanOrEqual(3, DB::table('account_audit_events')->where('event_type', 'accounts.tasks_saved')->count());
    }
}
