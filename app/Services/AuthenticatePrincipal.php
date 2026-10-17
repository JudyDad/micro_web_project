<?php

namespace App\Services;

use App\Models\WebPrincipal;
use App\Security\LegacyPasswordVerifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class AuthenticatePrincipal
{
    // Public synthetic values; misses still perform both supported KDF workloads.
    private const DUMMY_HASH = '$argon2id$v=19$m=65536,t=3,p=1$N3NJMHBGVWRUMFMyRUJ5Lg$sHlNUI9jVD2cZBUk/Xd47YpTn75rN+5zeqZrLRksEYU';

    private const DUMMY_LEGACY = 'AAECAwQFBgcICQoLDA0OD6rirHkgJUrCARvhwiO9AlEz7NUBCDqkiw29w+5zjOOk';

    public function __construct(private SourceCatalog $catalog, private SourceIdentity $identity, private LegacyPasswordVerifier $legacy, private AuditTrail $audit) {}

    public function attempt(int $source, string $username, string $password): ?WebPrincipal
    {
        if (! in_array($source, $this->catalog->sourceIds(), true)) {
            return null;
        }

        return DB::transaction(function () use ($source, $username, $password) {
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
            $deny = function () use ($source, $completeWork) {
                $completeWork();
                $this->audit->record($source, null, 'login.denied', 'denied');

                return null;
            };
            if (strlen($username) > 800 || strlen($password) > 4096 || preg_match('//u', $username) !== 1 || preg_match('//u', $password) !== 1) {
                return $deny();
            }
            // BINARY comparison preserves exact case and trailing spaces from the source.
            $users = $this->identity->publishedUsers($source)->whereRaw('BINARY u.UserName = ?', [$username])
                ->select(['u.record_id', 'u.Password'])->limit(2)->get();
            if ($users->count() !== 1) {
                return $deny();
            }
            $user = $users->first();
            $principal = WebPrincipal::where('source_id', $source)->where('source_table_id', 117)
                ->where('source_record_id', $user->record_id)->lockForUpdate()->first();
            if ($principal === null || $this->identity->activeUser($principal) === null) {
                return $deny();
            }
            try {
                if ($principal->password_hash !== null) {
                    $modernPerformed = str_starts_with($principal->password_hash, '$argon2id$');
                    $valid = Hash::check($password, $principal->password_hash);
                } else {
                    $encoded = str_replace([' ', "\t", "\r", "\n"], '', $user->Password);
                    $legacyPerformed = preg_match('/\A[A-Za-z0-9+\/]{64}\z/', $encoded) === 1;
                    $valid = $this->legacy->verify($password, $user->Password);
                }
            } catch (RuntimeException) {
                return $deny();
            }
            if (! $valid) {
                return $deny();
            }
            if ($principal->password_hash === null || Hash::needsRehash($principal->password_hash)) {
                $principal->password_hash = Hash::make($password);
                $modernPerformed = true;
                $principal->save();
                $this->audit->record($source, $principal->id, 'password.rehashed', 'succeeded', 'principal', $principal->id, 'policy:compatible-login-rehash');
            }
            $completeWork();
            $this->audit->record($source, $principal->id, 'login.succeeded', 'succeeded', 'principal', $principal->id);

            return $principal;
        }, attempts: 3);
    }
}
