<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Support\SyntheticFoundationFixture;
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

    public function test_source_qualified_login_and_read_only_city_workflows_fail_closed(): void
    {
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
        self::assertSame([], array_values(array_diff(array_column($tables, 'name'), ['migrations'])), 'Refusing to alter a pre-existing schema.');
        if ($tables !== []) {
            self::assertSame(0, DB::table('migrations')->count());
        }
        self::assertSame(0, Artisan::call('migrate', ['--force' => true]), Artisan::output());

        // All HTTP data changes, including audit writes and credential upgrades, are rolled back.
        DB::beginTransaction();
        try {
            $this->fixture = app(SyntheticFoundationFixture::class)->create();
            $this->assertLoginAndCityIsolation();
            $this->assertCredentialAndAccountState();
            $this->assertGrantAndPublicationRevocation();
            $this->assertOperationalConstraints();
            $this->assertAmbiguousSourceUserDenial();
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

    private function assertLoginAndCityIsolation(): void
    {
        $this->get('/ar/cities')->assertRedirect('/ar/login');
        $this->get('/en/cities/1/projects')->assertRedirect('/en/login');
        $this->get('/fr/login')->assertNotFound();
        $this->get('/en/login')->assertOk()->assertSee('password');
        $this->get('/ar/login')->assertOk()->assertSee('dir="rtl"', false);

        $this->login('aleppo');
        self::assertSame((string) $this->fixture['principals']['aleppo'], (string) Auth::id());
        $this->get('/en/cities')->assertOk()->assertSee('Aleppo')->assertDontSee('Damascus');
        $this->get('/en/cities/1/projects')->assertOk()->assertSee('1042')->assertSee('Synthetic Aleppo project')->assertDontSee('Synthetic Damascus project');
        $this->get('/ar/cities/1/projects')->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/en/cities/1/projects/'.$this->fixture['projects']['aleppo'])->assertOk()->assertSee('1042')->assertSee('Synthetic Aleppo project');
        $this->get('/en/cities/2/projects')->assertForbidden();
        $this->get('/en/cities/2/projects/'.$this->fixture['projects']['damascus'])->assertForbidden();
        $this->get('/en/cities/1/projects/'.$this->fixture['projects']['damascus'])->assertNotFound();
        $this->get('/en/cities/1/projects/999999999')->assertNotFound();
        foreach (['%', '_', "' OR 1=1 --", 'Damascus'] as $query) {
            $this->get('/en/cities/1/projects?'.http_build_query(['search' => $query]))->assertOk()->assertDontSee('Synthetic Aleppo project')->assertDontSee('Synthetic Damascus project');
        }
        $this->get('/en/cities/1/projects?search=1042')->assertOk()->assertSee('Synthetic Aleppo project');
        $this->assertSearchPaginationAndEscaping();
        $this->post('/en/cities/1/projects', ['MP_Name' => 'unwanted mutation'])->assertStatus(405);
        $this->get('/en/logout')->assertStatus(405);
        $this->logout();
        $this->get('/en/cities/1/projects')->assertRedirect('/en/login');

        $this->login('damascus');
        self::assertSame((string) $this->fixture['principals']['damascus'], (string) Auth::id());
        $this->get('/en/cities/2/projects/'.$this->fixture['projects']['damascus'])->assertOk()->assertSee('1042')->assertSee('Synthetic Damascus project')->assertDontSee('Synthetic Aleppo project');
        $this->get('/en/cities/1/projects')->assertForbidden();
        $this->logout();
    }

    private function assertSearchPaginationAndEscaping(): void
    {
        $template = (array) DB::table('legacy_a_microproject')->where('record_id', $this->fixture['projects']['aleppo'])->first();
        $registry = (array) DB::table('source_records')->where('id', $template['record_id'])->first();
        unset($registry['id']);
        for ($i = 1; $i <= 21; $i++) {
            $registry['source_key'] = 'synthetic:pagination:'.$i;
            $registry['key_sha256'] = hash('sha256', $registry['source_key'], true);
            $record = DB::table('source_records')->insertGetId($registry);
            DB::table('legacy_a_microproject')->insert(array_replace($template, ['record_id' => $record, 'MP_ID' => 1042 + $i, 'MP_Name' => 'Paged synthetic '.$i]));
        }
        $this->get('/ar/cities/1/projects?search=Paged')->assertOk()
            ->assertViewHas('projects', fn ($rows) => $rows->total() === 21 && $rows->count() === 20)
            ->assertSee('search=Paged&amp;page=2', false)->assertDontSee('Synthetic Damascus project');
        $this->get('/ar/cities/1/projects?search=Paged&page=2')->assertOk()
            ->assertViewHas('projects', fn ($rows) => $rows->count() === 1 && $rows->first()->MP_ID === 1063);
        $payload = '<script>window.untrustedProject=1</script>';
        DB::table('legacy_a_microproject')->where('record_id', $template['record_id'])->update(['MP_Name' => $payload]);
        $this->get('/en/cities/1/projects/'.$template['record_id'])->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        DB::table('legacy_a_microproject')->where('record_id', $template['record_id'])->update(['MP_Name' => $template['MP_Name']]);
        $this->get('/en/cities/1/projects/18446744073709551615')->assertNotFound();
    }

    private function assertCredentialAndAccountState(): void
    {
        $principal = $this->fixture['principals']['aleppo'];
        $hash = DB::table('web_principals')->where('id', $principal)->value('password_hash');
        self::assertNotNull($hash, 'First compatible desktop login must upgrade the web credential.');
        self::assertTrue(Hash::check(self::PASSWORD, $hash));
        $this->deniedLogin('aleppo', self::PASSWORD, 'Demo.User');
        $this->deniedLogin('aleppo', self::PASSWORD, ' demo.user');
        $this->deniedLogin('aleppo', self::PASSWORD, 'demo.user ');
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => Hash::make('Changed-Synthetic-Web-Password!')]);
        $this->deniedLogin('aleppo', self::PASSWORD);
        $this->login('aleppo', 'Changed-Synthetic-Web-Password!');
        $this->logout();
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => $hash, 'disabled_at' => now()]);
        $this->deniedLogin('aleppo');
        DB::table('web_principals')->where('id', $principal)->update(['disabled_at' => null]);

        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['UserRoleID' => 6]);
        $this->deniedLogin('aleppo');
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['UserRoleID' => 2]);
        $this->login('aleppo');
        DB::table('web_principals')->where('id', $principal)->update(['disabled_at' => now()]);
        $this->get('/en/cities/1/projects')->assertForbidden();
        DB::table('web_principals')->where('id', $principal)->update(['disabled_at' => null]);
        $this->logout();
        $legacy = DB::table('legacy_a_user')->where('UserName', 'demo.user')->value('Password');
        $longPassword = str_repeat('ع', 80).'A';
        $salt = str_repeat('s', 16);
        $legacyLong = base64_encode($salt.hash_pbkdf2('sha256', $longPassword, $salt, 100000, 32, true));
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['Password' => $legacyLong]);
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => null]);
        $this->login('aleppo', $longPassword);
        $upgraded = DB::table('web_principals')->where('id', $principal)->value('password_hash');
        self::assertStringStartsWith('$argon2id$', $upgraded);
        self::assertTrue(Hash::check($longPassword, $upgraded));
        self::assertFalse(Hash::check(str_repeat('ع', 80).'B', $upgraded), 'Suffixes beyond 72 bytes must remain significant.');
        $this->logout();
        DB::table('legacy_a_user')->where('UserName', 'demo.user')->update(['Password' => $legacy]);
        DB::table('web_principals')->where('id', $principal)->update(['password_hash' => $hash]);
    }

    private function assertGrantAndPublicationRevocation(): void
    {
        $principal = $this->fixture['principals']['aleppo'];
        $this->login('aleppo');
        DB::table('principal_capabilities')->where('principal_id', $principal)->update(['revoked_at' => now()]);
        $this->get('/en/cities/1/projects')->assertForbidden();
        $this->get('/en/cities/1/projects/'.$this->fixture['projects']['aleppo'])->assertForbidden();
        DB::table('principal_capabilities')->where('principal_id', $principal)->update(['revoked_at' => null]);

        $publication = (array) DB::table('source_publications')->where('source_id', 1)->first();
        DB::table('source_publications')->where('source_id', 1)->delete();
        $this->get('/en/cities/1/projects')->assertForbidden();
        $this->logout();
        $this->deniedLogin('aleppo');
        DB::table('source_publications')->insert($publication);

        // Publication replacement never silently rebinds a principal to a newer occurrence.
        $this->login('aleppo');
        $replacement = (array) DB::table('snapshots')->where('id', $publication['snapshot_id'])->first();
        unset($replacement['id']);
        $replacement['archive_sha256'] = str_repeat('z', 32);
        $replacement['sql_sha256'] = str_repeat('y', 32);
        $snapshot = DB::table('snapshots')->insertGetId($replacement);
        DB::table('source_publications')->where('source_id', 1)->update(['snapshot_id' => $snapshot]);
        $this->get('/en/cities/1/projects')->assertForbidden();
        $this->logout();
        $this->deniedLogin('aleppo');
        DB::table('source_publications')->where('source_id', 1)->update(['snapshot_id' => $publication['snapshot_id']]);
        DB::table('snapshots')->where('id', $snapshot)->delete();

        $this->login('aleppo');
        $originalEnvironment = $this->app['env'];
        try {
            $this->app['env'] = 'production';
            $this->get('/en/cities/1/projects')->assertForbidden();
        } finally {
            $this->app['env'] = $originalEnvironment;
        }
        $this->logout();
    }

    private function assertAmbiguousSourceUserDenial(): void
    {
        // Damascus intentionally has no unique username constraint in its retained schema.
        $existing = (array) DB::table('legacy_d_user')->where('UserName', 'demo.user')->first();
        $record = (array) DB::table('source_records')->where('id', $existing['record_id'])->first();
        unset($record['id']);
        $record['source_key'] = 'primary:synthetic-duplicate-username';
        $record['key_sha256'] = hash('sha256', $record['source_key'], true);
        $duplicate = DB::table('source_records')->insertGetId($record);
        $existing['record_id'] = $duplicate;
        $existing['UserID'] = 999999;
        DB::table('legacy_d_user')->insert($existing);
        $this->deniedLogin('damascus');
        DB::table('legacy_d_user')->where('record_id', $duplicate)->delete();
        DB::table('source_records')->where('id', $duplicate)->delete();
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
        $events = DB::table('audit_events')->get();
        self::assertGreaterThan(0, $events->count());
        $serialized = json_encode($events, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        foreach ([self::PASSWORD, 'Changed-Synthetic-Web-Password!', 'demo.user', 'Synthetic Aleppo project', 'Synthetic Damascus project'] as $sensitive) {
            self::assertStringNotContainsString($sensitive, $serialized);
        }
        foreach ($events as $event) {
            self::assertContains($event->outcome, ['succeeded', 'denied', 'failed']);
            self::assertTrue($event->actor_principal_id === null || (int) DB::table('web_principals')->where('id', $event->actor_principal_id)->value('source_id') === (int) $event->source_id);
        }
    }

    private function login(string $city, string $password = self::PASSWORD): void
    {
        app('cache')->flush();
        $previousSession = app('session')->getId();
        $this->post('/en/login', ['source_id' => $this->fixture['sources'][$city], 'username' => 'demo.user', 'password' => $password])->assertRedirect('/en/cities');
        $this->assertAuthenticated();
        self::assertNotSame($previousSession, app('session')->getId(), 'Successful login must rotate the session identifier.');
    }

    private function deniedLogin(string $city, string $password = self::PASSWORD, string $username = 'demo.user'): void
    {
        app('cache')->flush();
        $this->from('/en/login')->post('/en/login', ['source_id' => $this->fixture['sources'][$city], 'username' => $username, 'password' => $password])->assertRedirect('/en/login')->assertSessionHasErrors();
        $this->assertGuest();
        self::assertNull(session()->getOldInput('password'), 'Credentials must never be flashed to the session.');
    }

    private function logout(): void
    {
        $previousSession = app('session')->getId();
        $this->post('/en/logout')->assertRedirect('/en/login');
        $this->assertGuest();
        self::assertNotSame($previousSession, app('session')->getId(), 'Logout must invalidate the authenticated session.');
    }
}
