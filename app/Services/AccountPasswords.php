<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Password changes and resets of web accounts. A logged-in user changes the password without giving the old one; a user who forgot it
 * proves who they are with the user name and the e-mail stored for the account and receives a single-use reset link on screen (valid
 * for fifteen minutes, stored only as a SHA-256 hash). Nothing is sent by e-mail: delivery stays disabled.
 */
final class AccountPasswords
{
    public const MIN_LENGTH = 10;

    public const RESET_MINUTES = 15;

    public function __construct(private AccountAuditTrail $audit) {}

    /** Why a password is refused ('short', 'long', 'username'), or null. */
    public function violation(string $username, string $password): ?string
    {
        return match (true) {
            mb_strlen($password) < self::MIN_LENGTH => 'short',
            strlen($password) > 4096 || preg_match('//u', $password) !== 1 => 'long',
            mb_strtolower($password) === mb_strtolower($username) => 'username',
            default => null,
        };
    }

    /** An account created without a desktop identity must replace its first password before doing anything else. */
    public function mustChange(WebAccount $account): bool
    {
        $row = DB::table('web_accounts')->where('id', $account->id)->first(['password_changed_at', 'credential_principal_id', 'password_hash']);

        return $row !== null && $row->password_hash !== null && $row->credential_principal_id === null && $row->password_changed_at === null;
    }

    /** A random one-time password for an account an administrator creates. */
    public static function generate(): string
    {
        return Str::random(8).'-'.Str::random(8);
    }

    /** Sets a new password for the logged-in account (no old password is asked). Throws on a refused password. */
    public function change(WebAccount $account, string $password): void
    {
        $reason = $this->violation((string) $account->username, $password);
        if ($reason !== null) {
            throw new \InvalidArgumentException($reason);
        }
        $this->store($account->id, $password);
        $this->audit->record($account->id, 'password.changed', 'succeeded', subjectAccount: $account->id, subjectType: 'account', subjectId: $account->id);
    }

    private function store(int $accountId, string $password): void
    {
        $time = now('UTC')->format('Y-m-d H:i:s.u');
        DB::table('web_accounts')->where('id', $accountId)->update(['password_hash' => Hash::make($password), 'password_changed_at' => $time, 'updated_at' => $time]);
    }

    /**
     * The forgot-password check: the user name and e-mail must belong to one enabled account. Returns the plain single-use token (shown on
     * screen), or null when they do not match. Earlier unused tokens of the account stop working.
     */
    public function requestReset(string $username, string $email): ?string
    {
        $email = mb_strtolower(trim($email));
        $token = Str::random(40);
        $hash = hash('sha256', $token, true);
        $account = $username === '' || $email === '' || strlen($username) > 800 ? null
            : DB::table('web_accounts')->where('username', $username)->where('email', $email)->whereNull('disabled_at')->first(['id']);
        if ($account === null) {
            $this->audit->record(null, 'password.reset_denied', 'denied');

            return null;
        }

        return DB::transaction(function () use ($account, $token, $hash) {
            $now = now('UTC');
            DB::table('account_password_resets')->where('account_id', $account->id)->whereNull('used_at')->update(['used_at' => $now->format('Y-m-d H:i:s.u')]);
            DB::table('account_password_resets')->insert([
                'account_id' => $account->id, 'token_sha256' => $hash, 'requested_at' => $now->format('Y-m-d H:i:s.u'), 'expires_at' => $now->addMinutes(self::RESET_MINUTES)->format('Y-m-d H:i:s.u'), 'used_at' => null,
            ]);
            $this->audit->record(null, 'password.reset_requested', 'succeeded', subjectAccount: (int) $account->id, subjectType: 'account', subjectId: (int) $account->id);

            return $token;
        });
    }

    /** The reset record behind a token when it is unused, unexpired and its account is enabled. */
    private function open(string $token): ?object
    {
        if (preg_match('/\A[A-Za-z0-9]{40}\z/', $token) !== 1) {
            return null;
        }

        return DB::table('account_password_resets as r')->join('web_accounts as a', 'a.id', '=', 'r.account_id')->where('r.token_sha256', hash('sha256', $token, true))
            ->whereNull('r.used_at')->where('r.expires_at', '>', now('UTC')->format('Y-m-d H:i:s.u'))->whereNull('a.disabled_at')->first(['r.id', 'r.account_id', 'a.username']);
    }

    public function validToken(string $token): bool
    {
        return $this->open($token) !== null;
    }

    /** Sets the new password behind a valid token and spends it. Returns false for an invalid token; throws on a refused password. */
    public function reset(string $token, string $password): bool
    {
        return DB::transaction(function () use ($token, $password) {
            $record = $this->open($token);
            if ($record === null) {
                return false;
            }
            $reason = $this->violation((string) $record->username, $password);
            if ($reason !== null) {
                throw new \InvalidArgumentException($reason);
            }
            $this->store((int) $record->account_id, $password);
            DB::table('account_password_resets')->where('account_id', $record->account_id)->whereNull('used_at')->update(['used_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
            $this->audit->record((int) $record->account_id, 'password.reset', 'succeeded', subjectAccount: (int) $record->account_id, subjectType: 'account', subjectId: (int) $record->account_id);

            return true;
        });
    }
}
