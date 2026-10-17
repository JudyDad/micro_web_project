<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\WebAccount;
use App\Services\AccountProvisioner;
use App\Support\SyntheticAccountUpgrade;
use App\Support\SyntheticFoundationFixture;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

/** Full HTTP scenarios against synthetic data; never connects to an arbitrary application database. */
final class OperationalWorkflowTest extends TestCase
{
    private const PASSWORD = 'Synthetic-Desktop-2026!';

    private array $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        if (getenv('RUN_MYSQL_SCHEMA_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_MYSQL_SCHEMA_TESTS=1 for isolated MySQL workflow checks.');
        }

        self::assertTrue($this->app->environment('testing'));
        $host = getenv('MYSQL_TEST_HOST') ?: '127.0.0.1';
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '33072',
            'database.connections.mysql.database' => 'micro_projects_test',
            'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '',
            'database.connections.mysql.unix_socket' => '',
        ]);
        DB::purge('mysql');
        self::assertSame('micro_projects_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertStringNotContainsString('MariaDB', DB::selectOne('SELECT VERSION() AS v')->v);
    }

    public function test_unified_login_and_combined_city_workflows_fail_closed(): void
    {
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertSame([], array_values(array_diff(array_column($tables, 'name'), ['migrations'])), 'Refusing to alter a pre-existing schema.');
        if ($tables !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());
        DB::beginTransaction();
        try {
            $this->fixture = app(SyntheticFoundationFixture::class)->create();
            $this->assertSyntheticUpgradePreservesReviewedState();
            $this->assertWorkspace();
            $this->assertCredentialState();
            $this->assertPermissionsAndPublications();
            $this->assertConstraintsAndReviewedLinks();
            $this->assertOperationalConstraints();
            $this->assertAuditMinimization();
        } finally {
            Auth::forgetGuards();
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        self::assertSame(0, Artisan::call('migrate:rollback', ['--force' => true]), Artisan::output());
        self::assertSame(0, DB::table('migrations')->count());
    }

    private function assertWorkspace(): void
    {
        $this->get('/ar/projects')->assertRedirect('/ar/login');
        $this->get('/en/projects/1')->assertRedirect('/en/login');
        $this->get('/fr/login')->assertNotFound();
        $this->get('/en/login')->assertOk()->assertSee('password')->assertDontSee('name="source_id"', false);
        $this->get('/ar/login')->assertOk()->assertSee('dir="rtl"', false);

        // A former principal-session identity must not become an account identity with the same ID.
        $legacyKey = 'login_web_'.sha1(SessionGuard::class);
        $this->withSession([$legacyKey => $this->fixture['principals']['aleppo']]);
        Auth::forgetGuards();
        $this->get('/en/projects')->assertRedirect('/en/login');
        $this->login();
        self::assertSame((string) $this->fixture['account'], (string) Auth::id());
        $this->get('/en/cities')->assertRedirect('/en/projects');
        $this->get('/en/projects')->assertOk()->assertSee('Synthetic Aleppo project')->assertSee('Synthetic Damascus project')
            ->assertViewHas('projects', fn ($rows) => $rows->total() === 2);
        $this->get('/ar/projects')->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/en/projects?cities[]=1')->assertOk()->assertSee('Synthetic Aleppo project')->assertDontSee('Synthetic Damascus project');
        $this->get('/en/projects?cities[]=2')->assertOk()->assertSee('Synthetic Damascus project')->assertDontSee('Synthetic Aleppo project');
        $this->get('/en/projects?cities[]=3')->assertForbidden();
        $this->get('/en/projects?cities[]=1&cities[]=3')->assertForbidden();
        $this->get('/en/projects/'.$this->fixture['projects']['aleppo'])->assertOk()->assertSee('1042');
        $this->get('/en/projects/'.$this->fixture['projects']['damascus'])->assertOk()->assertSee('1042');
        $this->get('/en/cities/1/projects/'.$this->fixture['projects']['damascus'])->assertNotFound();
        $this->get('/en/cities/1/projects/'.$this->fixture['projects']['aleppo'])->assertRedirect('/en/projects/'.$this->fixture['projects']['aleppo']);
        $this->get('/en/projects/999999999')->assertNotFound();
        $this->get('/en/projects/18446744073709551615')->assertNotFound();
        foreach (['%', '_', "' OR 1=1 --"] as $query) {
            $this->get('/en/projects?'.http_build_query(['search' => $query]))->assertOk()->assertDontSee('Synthetic Aleppo project')->assertDontSee('Synthetic Damascus project');
        }
        $this->get('/en/projects?search=1042')->assertOk()->assertSee('Synthetic Aleppo project')->assertSee('Synthetic Damascus project');
        $this->assertPaginationAndEscaping();
        $this->post('/en/projects', ['MP_Name' => 'unwanted mutation'])->assertStatus(405);
        $this->get('/en/logout')->assertStatus(405);
        $this->logout();
        $this->get('/en/projects')->assertRedirect('/en/login');
    }

    private function assertSyntheticUpgradePreservesReviewedState(): void
    {
        $account = WebAccount::findOrFail($this->fixture['account']);
        $provisioner = app(AccountProvisioner::class);
        $provisioner->revoke($account, 2, 'projects.view', 'synthetic:test:revoke');
        $events = DB::table('account_audit_events')->count();
        $oldEvents = DB::table('audit_events')->count();
        $upgraded = app(SyntheticAccountUpgrade::class)->upgrade();
        self::assertSame($account->id, $upgraded->id);
        self::assertSame($events, DB::table('account_audit_events')->count());
        self::assertSame($oldEvents, DB::table('audit_events')->count());
        self::assertNotNull(DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', 2)->value('revoked_at'));
        self::assertSame(1, DB::table('web_accounts')->count());
        $provisioner->grant($account, 2, 'projects.view', 'synthetic:test:regrant');
        $snapshot = DB::table('snapshots')->where('source_id', 1)->first();
        DB::table('snapshots')->where('id', $snapshot->id)->update(['manifest_locator' => 'unreviewed:source']);
        try {
            app(SyntheticAccountUpgrade::class)->upgrade();
            self::fail('Unknown snapshots must not be treated as the reviewed synthetic fixture.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Unknown snapshot', $exception->getMessage());
        } finally {
            DB::table('snapshots')->where('id', $snapshot->id)->update(['manifest_locator' => $snapshot->manifest_locator]);
        }
        $environment = $this->app['env'];
        try {
            $this->app['env'] = 'production';
            app(SyntheticAccountUpgrade::class)->upgrade();
            self::fail('Synthetic upgrade cannot run in production.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('local/testing', $exception->getMessage());
        } finally {
            $this->app['env'] = $environment;
        }
    }

    private function assertPaginationAndEscaping(): void
    {
        $created = [];
        for ($i = 1; $i <= 21; $i++) {
            $city = $i % 2 === 0 ? 'damascus' : 'aleppo';
            $table = $city === 'aleppo' ? 'legacy_a_microproject' : 'legacy_d_microproject';
            $template = (array) DB::table($table)->where('record_id', $this->fixture['projects'][$city])->first();
            $registry = (array) DB::table('source_records')->where('id', $template['record_id'])->first();
            unset($registry['id']);
            $registry['source_key'] = 'synthetic:pagination:'.$i;
            $registry['key_sha256'] = hash('sha256', $registry['source_key'], true);
            $record = DB::table('source_records')->insertGetId($registry);
            DB::table($table)->insert(array_replace($template, ['record_id' => $record, 'MP_ID' => 1042 + $i, 'MP_Name' => 'Paged synthetic '.$i]));
            $created[] = [$table, $record];
        }
        $first = $this->get('/ar/projects?search=Paged&cities[]=1&cities[]=2')->assertOk()
            ->assertViewHas('projects', fn ($rows) => $rows->total() === 21 && $rows->count() === 20)
            ->viewData('projects')->pluck('record_id')->all();
        $second = $this->get('/ar/projects?search=Paged&cities[]=1&cities[]=2&page=2')->assertOk()
            ->assertViewHas('projects', fn ($rows) => $rows->count() === 1)->viewData('projects')->pluck('record_id')->all();
        self::assertCount(21, array_unique(array_merge($first, $second)));
        $this->get('/ar/projects?search=Paged&cities[]=2')->assertOk()->assertViewHas('projects', fn ($rows) => $rows->total() === 10);
        foreach ($created as [$table, $record]) {
            DB::table($table)->where('record_id', $record)->delete();
            DB::table('source_records')->where('id', $record)->delete();
        }
        $payload = '<script>window.untrustedProject=1</script>';
        DB::table('legacy_a_microproject')->where('record_id', $this->fixture['projects']['aleppo'])->update(['MP_Name' => $payload]);
        $this->get('/en/projects/'.$this->fixture['projects']['aleppo'])->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        DB::table('legacy_a_microproject')->where('record_id', $this->fixture['projects']['aleppo'])->update(['MP_Name' => 'Synthetic Aleppo project']);
    }

    private function assertCredentialState(): void
    {
        $account = $this->fixture['account'];
        $principal = $this->fixture['principals']['aleppo'];
        $hash = DB::table('web_accounts')->where('id', $account)->value('password_hash');
        self::assertStringStartsWith('$argon2id$', $hash);
        self::assertTrue(Hash::check(self::PASSWORD, $hash));
        foreach (['Demo.User', ' demo.user', 'demo.user '] as $name) {
            $this->deniedLogin(self::PASSWORD, $name);
        }
        DB::table('web_accounts')->where('id', $account)->update(['password_hash' => Hash::make('Changed-Synthetic-Web-Password!')]);
        $this->deniedLogin();
        $this->login('Changed-Synthetic-Web-Password!');
        $this->logout();
        DB::table('web_accounts')->where('id', $account)->update(['password_hash' => $hash, 'disabled_at' => now()]);
        $this->deniedLogin();
        DB::table('web_accounts')->where('id', $account)->update(['disabled_at' => null]);
        $this->login();
        DB::table('web_accounts')->where('id', $account)->update(['disabled_at' => now()]);
        $this->get('/en/projects')->assertForbidden();
        $this->assertGuest();
        DB::table('web_accounts')->where('id', $account)->update(['disabled_at' => null]);

        // Source roles cannot silently replace the reviewed account's modern grants.
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['UserRoleID' => 6]);
        $this->login();
        $this->get('/en/projects')->assertOk()->assertSee('Synthetic Aleppo project');
        $this->logout();
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['UserRoleID' => 2]);

        // A credential bridge must remain reviewed, and its modern hash never falls back.
        $principalHash = DB::table('web_principals')->where('id', $principal)->value('password_hash');
        DB::table('web_accounts')->where('id', $account)->update(['password_hash' => null]);
        DB::table('account_legacy_links')->where('principal_id', $principal)->update(['revoked_at' => now()]);
        $this->deniedLogin();
        DB::table('account_legacy_links')->where('principal_id', $principal)->update(['revoked_at' => null]);
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => Hash::make('Bridge-Modern-Only!')]);
        $this->deniedLogin();
        $this->login('Bridge-Modern-Only!');
        $this->logout();

        $legacy = DB::table('legacy_a_user')->where('UserName', 'demo.user')->value('Password');
        $longPassword = str_repeat('ع', 80).'A';
        $salt = str_repeat('s', 16);
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['Password' => base64_encode($salt.hash_pbkdf2('sha256', $longPassword, $salt, 100000, 32, true))]);
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => null]);
        DB::table('web_accounts')->where('id', $account)->update(['password_hash' => null]);
        $this->login($longPassword);
        $upgraded = DB::table('web_accounts')->where('id', $account)->value('password_hash');
        self::assertTrue(Hash::check($longPassword, $upgraded));
        self::assertFalse(Hash::check(str_repeat('ع', 80).'B', $upgraded));
        $this->logout();
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['Password' => $legacy]);
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => $principalHash]);
        DB::table('web_accounts')->where('id', $account)->update(['password_hash' => $hash]);
    }

    private function assertPermissionsAndPublications(): void
    {
        $account = $this->fixture['account'];
        $this->login();
        DB::table('account_city_capabilities')->where('account_id', $account)->where('city_id', 2)->update(['revoked_at' => now()]);
        $this->get('/en/projects')->assertOk()->assertSee('Synthetic Aleppo project')->assertDontSee('Damascus');
        $this->get('/en/projects?cities[]=2')->assertForbidden();
        $this->get('/en/projects/'.$this->fixture['projects']['damascus'])->assertForbidden();
        $this->get('/en/cities/2/projects')->assertForbidden();
        DB::table('account_city_capabilities')->where('account_id', $account)->where('city_id', 2)->update(['revoked_at' => null]);
        // Another capability in a city must not confer projects.view.
        DB::table('account_city_capabilities')->where('account_id', $account)->where('city_id', 2)->update(['capability' => 'projects.edit']);
        $this->get('/en/projects?cities[]=2')->assertForbidden();
        DB::table('account_city_capabilities')->where('account_id', $account)->where('city_id', 2)->update(['capability' => 'projects.view']);

        DB::table('account_city_capabilities')->where('account_id', $account)->update(['revoked_at' => now()]);
        $this->get('/en/projects')->assertOk()->assertViewHas('projects', fn ($rows) => $rows->total() === 0)->assertDontSee('Synthetic Aleppo project')->assertDontSee('Synthetic Damascus project');
        DB::table('account_city_capabilities')->where('account_id', $account)->update(['revoked_at' => null]);

        $publication = (array) DB::table('source_publications')->where('source_id', 1)->first();
        DB::table('source_publications')->where('source_id', 1)->delete();
        $this->get('/en/projects')->assertOk()->assertDontSee('Synthetic Aleppo project')->assertSee('Synthetic Damascus project');
        $this->get('/en/projects/'.$this->fixture['projects']['aleppo'])->assertNotFound();
        $this->logout();
        $this->login();
        DB::table('source_publications')->insert($publication);
        $environment = $this->app['env'];
        try {
            $this->app['env'] = 'production';
            $this->get('/en/projects')->assertOk()->assertViewHas('projects', fn ($rows) => $rows->total() === 0);
        } finally {
            $this->app['env'] = $environment;
        }
        $this->logout();
    }

    private function assertConstraintsAndReviewedLinks(): void
    {
        $account = (array) DB::table('web_accounts')->where('id', $this->fixture['account'])->first();
        unset($account['id']);
        $this->rejects(fn () => DB::table('web_accounts')->insert($account), 1062);
        $account['username'] = 'another.reviewed.person';
        $other = DB::table('web_accounts')->insertGetId($account);
        $link = (array) DB::table('account_legacy_links')->where('account_id', $this->fixture['account'])->first();
        unset($link['id']);
        $link['account_id'] = $other;
        $this->rejects(fn () => DB::table('account_legacy_links')->insert($link), 1062);
        $this->rejects(fn () => DB::table('account_legacy_links')->where('account_id', $this->fixture['account'])->update(['revoked_at' => '2000-01-01 00:00:00']), 3819);
        $this->rejects(fn () => DB::table('account_city_capabilities')->where('account_id', $this->fixture['account'])->update(['revoked_at' => '2000-01-01 00:00:00']), 3819);
        $account['username'] = 'credentialless';
        $account['password_hash'] = null;
        $account['credential_principal_id'] = null;
        $this->rejects(fn () => DB::table('web_accounts')->insert($account), 3819);
        DB::table('web_accounts')->where('id', $other)->delete();
        $before = DB::table('web_accounts')->count();
        try {
            app(AccountProvisioner::class)->provision('unreviewed.duplicate', $this->fixture['principals']['aleppo'], array_values($this->fixture['principals']), [1 => ['projects.view']], 'synthetic:test:duplicate');
            self::fail('A legacy principal already assigned to a person cannot be silently reassigned.');
        } catch (\InvalidArgumentException|RuntimeException|QueryException $exception) {
            self::assertSame($before, DB::table('web_accounts')->count(), 'Failed provisioning must roll back the entire account.');
        }
        foreach (glob(database_path('migrations/*create_*account*.php')) as $path) {
            $migration = require $path;
            try {
                $migration->down();
                self::fail('Rollback must refuse retained account evidence.');
            } catch (RuntimeException $exception) {
                self::assertStringContainsString('empty', $exception->getMessage());
            }
        }
        foreach (['web_accounts', 'account_legacy_links', 'account_city_capabilities', 'account_audit_events'] as $table) {
            self::assertTrue(DB::table($table)->exists());
        }
    }

    private function assertOperationalConstraints(): void
    {
        $grant = (array) DB::table('principal_capabilities')->where('principal_id', $this->fixture['principals']['aleppo'])->first();
        unset($grant['id']);
        $grant['source_id'] = 2;
        $this->rejects(fn () => DB::table('principal_capabilities')->insert($grant), 1452);

        $principal = (array) DB::table('web_principals')->where('id', $this->fixture['principals']['aleppo'])->first();
        unset($principal['id']);
        $principal['source_id'] = 2;
        $this->rejects(fn () => DB::table('web_principals')->insert($principal), 1452);
        $principal['source_id'] = 1;
        $principal['source_table_id'] = 116;
        $this->rejects(fn () => DB::table('web_principals')->insert($principal), 3819);

        $foreignSnapshot = DB::table('source_publications')->where('source_id', 2)->value('snapshot_id');
        $this->rejects(fn () => DB::table('source_publications')->where('source_id', 1)->update(['snapshot_id' => $foreignSnapshot]), 1452);
        $this->rejects(fn () => DB::table('source_publications')->where('source_id', 1)->update(['publication_kind' => 'unknown']), 3819);
        $this->rejects(fn () => DB::table('principal_capabilities')->where('principal_id', $this->fixture['principals']['aleppo'])->update(['revoked_at' => '2000-01-01 00:00:00']), 3819);

        $event = (array) DB::table('audit_events')->whereNotNull('actor_principal_id')->first();
        unset($event['id']);
        $event['source_id'] = (int) $event['source_id'] === 1 ? 2 : 1;
        $this->rejects(fn () => DB::table('audit_events')->insert($event), 1452);
        $event['actor_principal_id'] = null;
        $event['outcome'] = 'unknown';
        $this->rejects(fn () => DB::table('audit_events')->insert($event), 3819);

        $migration = require database_path('migrations/2026_10_17_000004_create_operational_foundation_tables.php');
        try {
            $migration->down();
            self::fail('Rollback must refuse retained operational evidence.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('empty', $exception->getMessage());
        }
        foreach (['web_principals', 'principal_capabilities', 'audit_events', 'source_publications'] as $table) {
            self::assertTrue(DB::table($table)->exists(), 'Rollback must preserve every operational table and its evidence.');
        }
    }

    private function rejects(callable $operation, int $mysqlCode): void
    {
        try {
            $operation();
        } catch (QueryException $exception) {
            self::assertSame($mysqlCode, (int) $exception->errorInfo[1], $exception->getMessage());

            return;
        }
        self::fail('MySQL accepted a row that should violate constraint '.$mysqlCode);
    }

    private function assertAuditMinimization(): void
    {
        $events = DB::table('account_audit_events')->get();
        self::assertGreaterThan(0, $events->count());
        $serialized = json_encode($events, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        foreach ([self::PASSWORD, 'Changed-Synthetic-Web-Password!', 'demo.user', 'Synthetic Aleppo project', 'Synthetic Damascus project'] as $sensitive) {
            self::assertStringNotContainsString($sensitive, $serialized);
        }
        foreach ($events as $event) {
            self::assertContains($event->outcome, ['succeeded', 'denied', 'failed']);
        }
        self::assertTrue(DB::table('account_audit_events')->where('event_type', 'login.succeeded')->whereNull('city_id')->exists());
    }

    private function login(string $password = self::PASSWORD): void
    {
        app('cache')->flush();
        $previousSession = app('session')->getId();
        $this->post('/en/login', ['username' => 'demo.user', 'password' => $password])->assertRedirect('/en/projects');
        $this->assertAuthenticated();
        self::assertNotSame($previousSession, app('session')->getId());
    }

    private function deniedLogin(string $password = self::PASSWORD, string $username = 'demo.user'): void
    {
        app('cache')->flush();
        $this->from('/en/login')->post('/en/login', ['username' => $username, 'password' => $password])->assertRedirect('/en/login')->assertSessionHasErrors();
        $this->assertGuest();
        self::assertNull(session()->getOldInput('password'));
    }

    private function logout(): void
    {
        $previousSession = app('session')->getId();
        $this->post('/en/logout')->assertRedirect('/en/login');
        $this->assertGuest();
        self::assertNotSame($previousSession, app('session')->getId());
    }
}
