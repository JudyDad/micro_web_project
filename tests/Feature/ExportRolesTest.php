<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\Applications\ApplicationService;
use App\Services\Attachments\AttachmentAccess;
use App\Services\Reports\Search\SearchModes;
use App\Services\Staff\LegacyStaffAdopter;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** The export rules decided on 27 October 2026: finance.export, reports.export, attachment categories, and the merged cashier and manager roles. */
final class ExportRolesTest extends TestCase
{
    private WebAccount $admin;

    private array $intake;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        app()->setLocale('en');
        config(['session.block_store' => 'array']);
        $this->admin = ApplicationFixture::account();
        FinanceFixture::grant($this->admin);
        $this->intake = ApplicationFixture::intake(1);
        $this->project = FinanceFixture::acceptedProject($this->admin, $this->intake);
    }

    private function holder(string $role): WebAccount
    {
        $account = StaffFixture::account($role.'.user');
        app(AccessRoles::class)->assign($account, 1, $role, 'synthetic:export-roles');

        return $account;
    }

    public function test_the_role_bundles_carry_the_export_grants_as_decided(): void
    {
        $has = fn (string $role, string $capability) => in_array($capability, AccessRoles::capabilities($role), true);
        foreach (AccountAccess::EXPORT_CAPABILITIES as $capability) {
            self::assertTrue($has('administrator', $capability), "administrator holds $capability");
        }
        self::assertSame([true, true, true, false, false], array_map(fn ($c) => $has('financial', $c), ['finance.export', 'reports.export', 'attachments.financial', 'attachments.personal', 'attachments.legal']));
        self::assertSame([false, true, false, true, true], array_map(fn ($c) => $has('data-entry', $c), ['finance.export', 'reports.export', 'attachments.financial', 'attachments.personal', 'attachments.legal']));
        self::assertSame([false, false, false, false, true], array_map(fn ($c) => $has('legal', $c), ['finance.export', 'reports.export', 'attachments.financial', 'attachments.personal', 'attachments.legal']));
        self::assertTrue($has('monitoring', 'reports.export'));
        foreach (['communication', 'media', 'guest'] as $role) {
            foreach (AccountAccess::EXPORT_CAPABILITIES as $capability) {
                self::assertFalse($has($role, $capability), "$role does not hold $capability");
            }
        }
        self::assertArrayNotHasKey('cashier', AccessRoles::ROLES, 'the cashier duty is the financial role');
        self::assertSame(['financial'], AccessRoles::RECIPIENTS['payment']);
    }

    public function test_every_export_follows_the_role_that_asks(): void
    {
        $person = $this->intake['person'];
        $routes = [
            'instalments' => '/en/payments/export', 'applications' => '/en/applications/export', 'statistics' => '/en/statistics/export', 'visit schedule' => '/en/visit-schedule/export',
            'task board' => '/en/task-board/export', 'loans report' => '/en/search-reports/export?mode=loans', 'family report' => '/en/search-reports/export?mode=family',
            'state history' => '/en/applications/'.$this->project.'/states/export', 'family members' => '/en/people/'.$person.'/family/export',
        ];
        $allowed = [
            'administrator' => array_keys($routes),
            'data-entry' => ['applications', 'statistics', 'visit schedule', 'task board', 'family report', 'state history', 'family members'],
            'financial' => ['instalments', 'applications', 'task board', 'loans report', 'family report', 'state history', 'family members'],
            'monitoring' => ['applications', 'visit schedule', 'task board', 'state history'],
            'legal' => [], 'media' => [], 'guest' => [], 'communication' => [],
        ];
        foreach ($allowed as $role => $may) {
            $this->actingAs($this->holder($role));
            foreach ($routes as $name => $url) {
                $status = $this->get($url.(str_contains($url, '?') ? '&' : '?').'format=csv')->getStatusCode();
                if (in_array($name, $may, true)) {
                    self::assertSame(200, $status, "$role may export $name");
                } else {
                    self::assertContains($status, [403, 404], "$role may not export $name (got $status)");
                    self::assertSame(403, $status, "$role is refused the $name export");
                }
            }
        }
    }

    public function test_the_pages_stay_open_where_only_the_export_is_refused(): void
    {
        foreach (['legal' => ['/en/payments', '/en/applications/'.$this->project], 'communication' => ['/en/task-board', '/en/applications/'.$this->project]] as $role => $pages) {
            $this->actingAs($this->holder($role));
            foreach ($pages as $page) {
                $this->get($page)->assertOk();
            }
        }
    }

    public function test_money_exports_need_the_finance_export_grant_even_with_the_export_grant(): void
    {
        $account = StaffFixture::account('partial.user');
        foreach (['finance.view', 'applications.view', 'reports.export'] as $capability) {
            DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => 1, 'capability' => $capability, 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        }
        $this->actingAs($account);
        $this->get('/en/payments')->assertOk();
        $this->get('/en/payments/export?format=csv')->assertForbidden();
        $this->get('/en/search-reports/export?mode=loans&format=csv')->assertForbidden();
        DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => 1, 'capability' => 'finance.export', 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        $this->get('/en/payments/export?format=csv')->assertOk();
        $this->get('/en/search-reports/export?mode=loans&format=csv')->assertOk();
    }

    public function test_an_export_is_limited_to_the_cities_where_the_account_holds_its_grant(): void
    {
        $account = StaffFixture::account('two-cities.user');
        foreach ([1, 2] as $city) {
            foreach (['applications.view', 'applications.manage'] as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
            }
        }
        DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => 1, 'capability' => 'reports.export', 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        app(ApplicationService::class)->create($this->admin, 2, ['c_MP_Name' => 'City two', 'c_MP_DateOfRequest' => '2026-10-19'], ApplicationFixture::intake(2)['person'], ApplicationFixture::intake(2)['references']);
        $this->actingAs($account);
        $csv = $this->get('/en/applications/export?format=csv')->assertOk()->streamedContent();
        self::assertStringContainsString('Aleppo', $csv);
        self::assertStringNotContainsString('Damascus', $csv, 'a city without the export grant is left out');
        $this->get('/en/applications/export?cities[]=2&format=csv')->assertForbidden();
        $this->get('/en/applications')->assertOk()->assertSee('City two');
    }

    public function test_the_attachment_report_lists_only_the_categories_the_account_holds(): void
    {
        $category = fn (string $name) => $name;
        $types = [];
        foreach (['ثبوتيات شخصية - Personal' => 'ID card', 'أوراق قانونية - Legal' => 'Contract', 'أوراق مالية - Financial' => 'Invoice', 'مرفقات أخرى' => 'Other'] as $cat => $name) {
            $types[$name] = ApplicationFixture::row('imagetype', 1, ['c_ID' => count($types) + 1, 'c_Name' => $name, 'c_Name_en' => $name, 'c_Category' => $category($cat)]);
            ApplicationFixture::row('image', 1, ['c_image_NewPath' => 'secret/'.$name.'.jpg'], ['MicroProject_ID' => $this->project, 'ImageType_ID' => $types[$name]]);
        }
        $seen = function (WebAccount $account): array {
            $search = SearchModes::make('attachments');
            $rows = $search->page($account, ['mode' => 'attachments'])['rows']->getCollection()->all();
            $names = array_map(fn ($row) => $row[5], $rows);
            sort($names);

            return $names;
        };
        self::assertSame(['Contract', 'ID card', 'Invoice', 'Other'], $seen($this->holder('administrator')));
        self::assertSame(['Contract', 'ID card'], $seen($this->holder('data-entry')));
        self::assertSame(['Invoice'], $seen($this->holder('financial')));
        self::assertSame(['Contract'], $seen($this->holder('legal')));

        $nothing = StaffFixture::account('download-only.user');
        foreach (['attachments.download', 'projects.view', 'applications.view', 'reports.export'] as $capability) {
            DB::table('account_city_capabilities')->insert(['account_id' => $nothing->id, 'city_id' => 1, 'capability' => $capability, 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        }
        $this->actingAs($nothing);
        $this->get('/en/search-reports?mode=attachments')->assertForbidden();

        $this->actingAs($this->holder('financial'));
        $page = $this->get('/en/search-reports?mode=attachments')->assertOk();
        self::assertStringContainsString('Invoice', $page->getContent());
        self::assertStringNotContainsString('ID card', $page->getContent());
        self::assertStringNotContainsString('secret', $page->getContent());
        $csv = $this->get('/en/search-reports/export?mode=attachments&format=csv')->assertOk()->streamedContent();
        self::assertStringContainsString('Invoice', $csv);
        self::assertStringNotContainsString('Contract', $csv);
    }

    public function test_an_image_downloads_only_with_its_categorys_grant(): void
    {
        $mapping = OperationalManifest::mapping('Aleppo', 'imagetype')['preservation_table'];
        Schema::create($mapping, function ($table) {
            $table->bigInteger('record_id')->nullable();
            $table->bigInteger('origin_source_id')->nullable();
            $table->bigInteger('ID')->nullable();
            $table->text('Category')->nullable();
        });
        foreach ([1 => 'ثبوتيات شخصية - Personal', 2 => 'أوراق قانونية - Legal', 3 => 'أوراق مالية - Financial', 4 => 'غير معروف'] as $id => $category) {
            DB::table($mapping)->insert(['record_id' => $id, 'origin_source_id' => 1, 'ID' => $id, 'Category' => $category]);
        }
        $check = new \ReflectionMethod(AttachmentAccess::class, 'requireCategory');
        $try = function (WebAccount $account, int $type) use ($check): int {
            try {
                $check->invoke(app(AttachmentAccess::class), $account, 1, 'Aleppo', 1, $type);

                return 200;
            } catch (HttpException $e) {
                return $e->getStatusCode();
            }
        };
        $matrix = ['administrator' => [200, 200, 200, 200], 'data-entry' => [200, 200, 403, 403], 'financial' => [403, 403, 200, 403], 'legal' => [403, 200, 403, 403]];
        foreach ($matrix as $role => $expected) {
            $account = $this->holder($role);
            self::assertSame($expected, [$try($account, 1), $try($account, 2), $try($account, 3), $try($account, 4)], "$role: personal, legal, financial, unknown category");
        }
        self::assertSame(403, $try($this->holder('financial'), 99), 'an image whose type is missing needs every category grant');
        self::assertSame(200, $try($this->holder('administrator'), 99));
        self::assertGreaterThanOrEqual(5, DB::table('account_audit_events')->where('event_type', 'attachments.download.denied')->count(), 'each refusal is audited');
    }

    public function test_the_sync_command_merges_cashiers_and_adds_only_the_new_grants_once(): void
    {
        $cashier = StaffFixture::account('old.cashier', [1 => ['cashier']]);
        foreach (['projects.view', 'applications.view', 'finance.view', 'notifications.view'] as $capability) {
            DB::table('account_city_capabilities')->insert(['account_id' => $cashier->id, 'city_id' => 1, 'capability' => $capability, 'decision_locator' => 'synthetic:old', 'granted_at' => now('UTC')]);
        }
        $financial = StaffFixture::account('old.financial', [1 => ['financial']]);
        foreach (['projects.view', 'finance.view', 'finance.manage'] as $capability) {
            DB::table('account_city_capabilities')->insert(['account_id' => $financial->id, 'city_id' => 1, 'capability' => $capability, 'decision_locator' => 'synthetic:old', 'granted_at' => now('UTC')]);
        }
        $data = StaffFixture::account('old.data', [1 => ['data-entry']]);
        // A new grant an administrator had already revoked by hand is not given back.
        DB::table('account_city_capabilities')->insert(['account_id' => $data->id, 'city_id' => 1, 'capability' => 'attachments.personal', 'decision_locator' => 'synthetic:old', 'granted_at' => now('UTC'), 'revoked_at' => now('UTC')]);

        $this->artisan('staff:sync-export-roles', ['--dry-run' => true])->expectsOutputToContain('cashier roles merged')->assertExitCode(0);
        self::assertSame(['cashier'], DB::table('account_city_roles')->where('account_id', $cashier->id)->whereNull('revoked_at')->pluck('role')->all(), 'a dry run changes nothing');

        $this->artisan('staff:sync-export-roles')->assertExitCode(0);
        $roles = app(AccessRoles::class);
        self::assertSame(['financial'], $roles->roles($cashier, 1), 'the cashier assignment ended; the account is financial');
        $access = app(AccountAccess::class);
        foreach (['finance.export', 'reports.export', 'attachments.financial', 'finance.manage', 'attachments.download'] as $capability) {
            self::assertTrue($access->check($cashier, 1, $capability), "the former cashier holds $capability");
        }
        self::assertTrue($access->check($financial, 1, 'finance.export'));
        self::assertTrue($access->check($financial, 1, 'attachments.financial'));
        self::assertTrue($access->check($data, 1, 'reports.export'));
        self::assertTrue($access->check($data, 1, 'attachments.legal'));
        self::assertFalse($access->check($data, 1, 'attachments.personal'), 'a grant revoked by hand stays revoked');
        self::assertFalse($access->check($data, 1, 'finance.export'), 'a role gets only the grants its bundle carries');

        $before = DB::table('account_city_capabilities')->count();
        $this->artisan('staff:sync-export-roles')->assertExitCode(0);
        self::assertSame($before, DB::table('account_city_capabilities')->count(), 'repeating it adds nothing');
        self::assertTrue(DB::table('account_audit_events')->where('event_type', 'capability.granted')->exists());
        $this->artisan('staff:sync-export-roles', ['--decision' => 'bad decision!'])->assertExitCode(1);
    }

    public function test_a_desktop_manager_is_an_administrator_and_the_cashier_flag_is_the_financial_role(): void
    {
        self::assertSame('administrator', LegacyStaffAdopter::ROLES[5]);
        self::assertSame('financial', LegacyStaffAdopter::DUTIES['IsCashier']);
        self::assertSame('administrator', LegacyStaffAdopter::ROLES[8]);
    }
}
