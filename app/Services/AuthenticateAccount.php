<?php

namespace App\Services;

use App\Models\WebAccount;
use App\Models\WebPrincipal;
use App\Security\LegacyPasswordVerifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class AuthenticateAccount
{
    private const DUMMY_HASH = '$argon2id$v=19$m=65536,t=3,p=1$N3NJMHBGVWRUMFMyRUJ5Lg$sHlNUI9jVD2cZBUk/Xd47YpTn75rN+5zeqZrLRksEYU';

    private const DUMMY_LEGACY = 'AAECAwQFBgcICQoLDA0OD6rirHkgJUrCARvhwiO9AlEz7NUBCDqkiw29w+5zjOOk';

    public function __construct(private SourceIdentity $identity, private LegacyPasswordVerifier $legacy, private AccountAuditTrail $audit) {}

    public function attempt(string $username, string $password): ?WebAccount
    {
        return DB::transaction(function () use ($username, $password) {
            $legacyPerformed = false;
            $modernPerformed = false;
            $completeWork = function () use ($password, &$legacyPerformed, &$modernPerformed) {
                if (! $legacyPerformed) {
                    $this->legacy->verify(substr($password, 0, 4096), self::DUMMY_LEGACY);
                    $legacyPerformed = true;
                }
                if (! $modernPerformed) {
                    password_verify(substr($password, 0, 4096), self::DUMMY_HASH);
                    $modernPerformed = true;
                }
            };
            $deny = function () use ($completeWork) {
                $completeWork();
                $this->audit->record(null, 'login.denied', 'denied');

                return null;
            };
            if ($username === '' || strlen($username) > 800 || mb_strlen($username) > 200 || strlen($password) > 4096
                || preg_match('//u', $username) !== 1 || preg_match('//u', $password) !== 1) {
                return $deny();
            }
            $account = WebAccount::where('username', $username)->lockForUpdate()->first();
            if ($account === null || $account->disabled_at !== null) {
                return $deny();
            }
            $hash = $account->password_hash;
            $legacyHash = null;
            if ($hash === null) {
                $link = DB::table('account_legacy_links')->where('account_id', $account->id)
                    ->where('principal_id', $account->credential_principal_id)->whereNull('revoked_at')->sharedLock()->first();
                $principal = $link === null ? null : WebPrincipal::whereKey($link->principal_id)->sharedLock()->first();
                if ($principal === null || $principal->source_id !== (int) $link->source_id || $this->identity->activeUser($principal) === null) {
                    return $deny();
                }
                $hash = $principal->password_hash;
                if ($hash === null) {
                    $legacyHash = $this->identity->publishedUsers($principal->source_id)->where('r.id', $principal->source_record_id)->value('u.Password');
                }
            }
            try {
                if ($hash !== null) {
                    // Reviewed preserved modern hashes may predate the Argon2id policy.
                    $modernPerformed = str_starts_with($hash, '$argon2id$');
                    $algorithm = password_get_info($hash)['algoName'];
                    $valid = $algorithm !== 'unknown' && ! ($algorithm === 'bcrypt' && strlen($password) > 72)
                        && password_verify($password, $hash);
                } else {
                    $encoded = str_replace([' ', "\t", "\r", "\n"], '', (string) $legacyHash);
                    $legacyPerformed = preg_match('/\A[A-Za-z0-9+\/]{64}\z/', $encoded) === 1;
                    $valid = $this->legacy->verify($password, (string) $legacyHash);
                }
            } catch (RuntimeException) {
                return $deny();
            }
            if (! $valid) {
                return $deny();
            }
            if ($account->password_hash === null || Hash::needsRehash($account->password_hash)) {
                $account->password_hash = Hash::make($password);
                $modernPerformed = true;
                $account->save();
                $this->audit->record($account->id, 'password.rehashed', 'succeeded', subjectAccount: $account->id, subjectType: 'account', subjectId: $account->id);
            }
            $completeWork();
            $this->audit->record($account->id, 'login.succeeded', 'succeeded', subjectAccount: $account->id, subjectType: 'account', subjectId: $account->id);

            return $account;
        }, attempts: 3);
    }
}
