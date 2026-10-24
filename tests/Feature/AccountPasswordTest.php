<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\AccountPasswords;
use App\Services\AuthenticateAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\ApplicationFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class AccountPasswordTest extends TestCase
{
    private const OLD = 'Old-password-2026';

    private WebAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = StaffFixture::account('staff.member', [1 => ['guest']], 'Staff.Member@Example.org');
        StaffFixture::grant($this->account, [1], ['projects.view', 'people.view', 'applications.view']);
        DB::table('web_accounts')->where('id', $this->account->id)->update(['password_hash' => Hash::make(self::OLD)]);
    }

    private function hash(): string
    {
        return (string) DB::table('web_accounts')->where('id', $this->account->id)->value('password_hash');
    }

    private function token(): string
    {
        $redirect = $this->post('/en/forgot-password', ['username' => 'staff.member', 'email' => 'staff.member@example.org'])->assertRedirect()->headers->get('Location');
        self::assertMatchesRegularExpression('#/en/reset-password/[A-Za-z0-9]{40}$#', $redirect);

        return substr($redirect, -40);
    }

    public function test_a_logged_in_user_changes_the_password_without_giving_the_old_one(): void
    {
        $this->actingAs($this->account);
        $this->get('/en/account/password')->assertOk()->assertSee('Change password')->assertDontSee('Old password')->assertDontSee('created with a one-time password');
        $this->get('/ar/account/password')->assertOk()->assertSee('تغيير كلمة المرور');

        $this->post('/en/account/password', ['password' => 'New-password-2026', 'password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $this->post('/en/account/password', ['password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post('/en/account/password', ['password' => 'STAFF.MEMBER', 'password_confirmation' => 'STAFF.MEMBER'])->assertSessionHasErrors('password');
        self::assertTrue(Hash::check(self::OLD, $this->hash()), 'refused passwords change nothing');

        $this->post('/en/account/password', ['password' => 'New-password-2026', 'password_confirmation' => 'New-password-2026'])->assertRedirect('/en/projects');
        self::assertTrue(Hash::check('New-password-2026', $this->hash()));
        self::assertNotNull(DB::table('web_accounts')->where('id', $this->account->id)->value('password_changed_at'));
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'password.changed')->where('actor_account_id', $this->account->id)->count());
        self::assertSame($this->account->id, app(AuthenticateAccount::class)->attempt('staff.member', 'New-password-2026')?->id);
        self::assertNull(app(AuthenticateAccount::class)->attempt('staff.member', self::OLD));
    }

    public function test_an_account_with_a_one_time_password_must_choose_its_own_first(): void
    {
        $plain = StaffFixture::account('fresh.account', [1 => ['guest']]);
        StaffFixture::grant($plain, [1], ['projects.view', 'applications.view', 'people.view']);
        DB::table('web_accounts')->where('id', $plain->id)->update(['password_changed_at' => null, 'password_hash' => Hash::make('One-time-pass')]);
        self::assertTrue(app(AccountPasswords::class)->mustChange($plain));
        $this->actingAs($plain);
        $this->get('/en/projects')->assertRedirect('/en/account/password');
        $this->get('/en/applications')->assertRedirect('/en/account/password');
        $this->get('/en/account/password')->assertOk()->assertSee('one-time password');
        $this->post('/en/account/password', ['password' => 'Chosen-password-1', 'password_confirmation' => 'Chosen-password-1'])->assertRedirect('/en/projects');
        self::assertFalse(app(AccountPasswords::class)->mustChange($plain));
        $this->get('/en/applications')->assertOk();

        // Accounts that inherit a desktop password, and legacy-style rows without a hash, are never forced.
        $inherited = StaffFixture::account('inherited');
        DB::table('web_accounts')->where('id', $inherited->id)->update(['password_changed_at' => null, 'credential_principal_id' => 5]);
        self::assertFalse(app(AccountPasswords::class)->mustChange($inherited));
    }

    public function test_forgot_password_opens_a_single_use_reset_after_the_user_name_and_e_mail_match(): void
    {
        $this->get('/en/login')->assertOk()->assertSee('Forgot your password?');
        $this->get('/en/forgot-password')->assertOk()->assertSee('e-mail address stored for your account');
        $this->get('/ar/forgot-password')->assertOk()->assertSee('dir="rtl"', false)->assertSee('نسيت كلمة المرور');
        foreach ([['username' => 'staff.member', 'email' => 'someone.else@example.org'], ['username' => 'nobody', 'email' => 'staff.member@example.org']] as $bad) {
            $this->post('/en/forgot-password', $bad)->assertSessionHasErrors('username');
        }
        $this->post('/en/forgot-password', ['username' => 'staff.member', 'email' => ''])->assertSessionHasErrors('email');
        self::assertSame(0, DB::table('account_password_resets')->count(), 'a mismatch opens nothing');
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'password.reset_denied')->count());

        $token = $this->token();
        self::assertSame(1, DB::table('account_password_resets')->count());
        self::assertNotSame($token, bin2hex((string) DB::table('account_password_resets')->value('token_sha256')), 'only a hash is stored');
        $this->get("/en/reset-password/{$token}")->assertOk()->assertSee('Choose a new password')->assertDontSee('no longer valid');
        $this->post("/en/reset-password/{$token}", ['password' => 'Brand-new-pass-1', 'password_confirmation' => 'nope'])->assertSessionHasErrors('password');
        $this->post("/en/reset-password/{$token}", ['password' => 'tiny', 'password_confirmation' => 'tiny'])->assertSessionHasErrors('password');
        self::assertTrue(Hash::check(self::OLD, $this->hash()));
        $this->post("/en/reset-password/{$token}", ['password' => 'Brand-new-pass-1', 'password_confirmation' => 'Brand-new-pass-1'])->assertRedirect('/en/login');
        self::assertTrue(Hash::check('Brand-new-pass-1', $this->hash()));
        self::assertSame($this->account->id, app(AuthenticateAccount::class)->attempt('staff.member', 'Brand-new-pass-1')?->id);
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'password.reset')->count());

        // The link is spent.
        $this->get("/en/reset-password/{$token}")->assertOk()->assertSee('not valid any more');
        $this->post("/en/reset-password/{$token}", ['password' => 'Another-pass-123', 'password_confirmation' => 'Another-pass-123'])->assertRedirect('/en/forgot-password');
        self::assertTrue(Hash::check('Brand-new-pass-1', $this->hash()));
    }

    public function test_tokens_expire_a_newer_request_replaces_the_older_one_and_disabled_accounts_cannot_reset(): void
    {
        $first = $this->token();
        $second = $this->token();
        $this->get("/en/reset-password/{$first}")->assertSee('not valid any more');
        $this->get("/en/reset-password/{$second}")->assertSee('Choose a new password');

        DB::table('account_password_resets')->update(['requested_at' => now('UTC')->subMinutes(30), 'expires_at' => now('UTC')->subMinutes(15)]);
        $this->get("/en/reset-password/{$second}")->assertSee('not valid any more');
        $this->post("/en/reset-password/{$second}", ['password' => 'Brand-new-pass-1', 'password_confirmation' => 'Brand-new-pass-1'])->assertRedirect('/en/forgot-password');
        self::assertTrue(Hash::check(self::OLD, $this->hash()));

        $third = $this->token();
        DB::table('web_accounts')->where('id', $this->account->id)->update(['disabled_at' => now()]);
        $this->get("/en/reset-password/{$third}")->assertSee('not valid any more');
        $this->post('/en/forgot-password', ['username' => 'staff.member', 'email' => 'staff.member@example.org'])->assertSessionHasErrors('username');
        $this->get('/en/reset-password/short')->assertNotFound();
    }
}
