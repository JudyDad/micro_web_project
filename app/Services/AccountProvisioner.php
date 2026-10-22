<?php

namespace App\Services;

use App\Models\WebAccount;
use App\Models\WebPrincipal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Controlled administrative service. It writes accounts, reviewed links and city capabilities, so only the staff administration screens
 * (`accounts.manage`, see AccountAdminService) and provisioning commands reach it; the read-only runtime never does.
 */
final class AccountProvisioner
{
    public function __construct(private SourceIdentity $identity, private AccountAuditTrail $audit) {}

    public function provision(string $username, int $credentialPrincipalId, array $reviewedPrincipalIds, array $cityCapabilities, string $decision, ?int $actor = null): WebAccount
    {
        if ($username === '' || strlen($username) > 800 || preg_match('//u', $username) !== 1 || mb_strlen($username) > 200
            || ! AccountAuditTrail::validDecision($decision) || $credentialPrincipalId < 1 || $reviewedPrincipalIds === []
            || ! in_array($credentialPrincipalId, $reviewedPrincipalIds, true)
            || count(array_unique($reviewedPrincipalIds)) !== count($reviewedPrincipalIds)) {
            throw new InvalidArgumentException('Explicit reviewed account identity and decision are required.');
        }
        foreach ($reviewedPrincipalIds as $id) {
            if (! is_int($id) || $id < 1) {
                throw new InvalidArgumentException('Reviewed principal IDs must be positive integers.');
            }
        }

        return DB::transaction(function () use ($username, $credentialPrincipalId, $reviewedPrincipalIds, $cityCapabilities, $decision, $actor) {
            sort($reviewedPrincipalIds);
            $principals = WebPrincipal::whereIn('id', $reviewedPrincipalIds)->orderBy('id')->lockForUpdate()->get();
            if ($principals->count() !== count($reviewedPrincipalIds)
                || DB::table('account_legacy_links')->whereIn('principal_id', $reviewedPrincipalIds)->exists()) {
                throw new InvalidArgumentException('A reviewed principal is missing or already linked.');
            }
            foreach ($principals as $principal) {
                if ($this->identity->activeUser($principal) === null) {
                    throw new InvalidArgumentException('Inactive legacy identity requires a separate staff decision.');
                }
                if ($principal->id === $credentialPrincipalId) {
                    if ($principal->password_hash !== null) {
                        if (preg_match('//u', $principal->password_hash) !== 1 || password_get_info($principal->password_hash)['algoName'] === 'unknown') {
                            throw new InvalidArgumentException('Selected credential needs compatibility review.');
                        }
                    } else {
                        $legacy = $this->identity->publishedUsers($principal->source_id)->where('r.id', $principal->source_record_id)->value('u.Password');
                        $encoded = str_replace([' ', "\t", "\r", "\n"], '', (string) $legacy);
                        if (preg_match('/\A[A-Za-z0-9+\/]{64}\z/', $encoded) !== 1 || strlen((string) base64_decode($encoded, true)) !== 48) {
                            throw new InvalidArgumentException('Selected credential needs compatibility review.');
                        }
                    }
                }
            }
            $account = new WebAccount;
            $account->username = $username;
            $account->credential_principal_id = $credentialPrincipalId;
            $account->save();
            $this->audit->record($actor, 'account.provisioned', 'succeeded', subjectAccount: $account->id, subjectType: 'account', subjectId: $account->id, decision: $decision);
            foreach ($principals as $principal) {
                $linkId = DB::table('account_legacy_links')->insertGetId([
                    'account_id' => $account->id, 'source_id' => $principal->source_id, 'principal_id' => $principal->id,
                    'decision_locator' => $decision, 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u'), 'revoked_at' => null,
                ]);
                $this->audit->record($actor, 'legacy.linked', 'succeeded', subjectAccount: $account->id, subjectType: 'legacy_link', subjectId: $linkId, decision: $decision);
            }
            foreach ($cityCapabilities as $city => $capabilities) {
                if (! is_int($city) || ! is_array($capabilities)) {
                    throw new InvalidArgumentException('City capabilities must map integer city IDs to capability lists.');
                }
                foreach ($capabilities as $capability) {
                    $this->grant($account, $city, $capability, $decision, $actor);
                }
            }

            return $account;
        }, attempts: 3);
    }

    public function grant(WebAccount $account, int $city, string $capability, string $decision, ?int $actor = null): void
    {
        $this->changeGrant($account, $city, $capability, $decision, $actor, false);
    }

    public function revoke(WebAccount $account, int $city, string $capability, string $decision, ?int $actor = null): void
    {
        $this->changeGrant($account, $city, $capability, $decision, $actor, true);
    }

    private function changeGrant(WebAccount $account, int $city, string $capability, string $decision, ?int $actor, bool $revoke): void
    {
        if (! in_array($capability, AccountAccess::CAPABILITIES, true) || ! AccountAuditTrail::validDecision($decision)
            || ! DB::table('cities')->where('id', $city)->exists()) {
            throw new InvalidArgumentException('Invalid explicit city capability assignment.');
        }
        DB::transaction(function () use ($account, $city, $capability, $decision, $actor, $revoke) {
            WebAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $query = DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', $city)->where('capability', $capability);
            $grant = (clone $query)->lockForUpdate()->first();
            $time = now('UTC')->format('Y-m-d H:i:s.u');
            if ($revoke) {
                if ($grant === null) {
                    throw new InvalidArgumentException('Cannot revoke a missing city capability.');
                }
                $query->update(['revoked_at' => $time]);
                $id = $grant->id;
            } else {
                $values = ['decision_locator' => $decision, 'granted_at' => $time, 'revoked_at' => null];
                if ($grant === null) {
                    $id = DB::table('account_city_capabilities')->insertGetId($values + ['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability]);
                } else {
                    $query->update($values);
                    $id = $grant->id;
                }
            }
            $this->audit->record($actor, $revoke ? 'capability.revoked' : 'capability.granted', 'succeeded', $city, $account->id, 'capability_grant', $id, $decision);
        }, attempts: 3);
    }

    public function setLinkRevoked(WebAccount $account, int $principalId, bool $revoked, string $decision, ?int $actor = null): void
    {
        if (! AccountAuditTrail::validDecision($decision)) {
            throw new InvalidArgumentException('A link review decision is required.');
        }
        DB::transaction(function () use ($account, $principalId, $revoked, $decision, $actor) {
            WebAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $query = DB::table('account_legacy_links')->where('account_id', $account->id)->where('principal_id', $principalId);
            $link = (clone $query)->lockForUpdate()->first();
            if ($link === null) {
                throw new InvalidArgumentException('No reviewed link exists.');
            }
            if (! $revoked) {
                $principal = WebPrincipal::whereKey($principalId)->lockForUpdate()->first();
                if ($principal === null || $this->identity->activeUser($principal) === null) {
                    throw new InvalidArgumentException('Inactive legacy identity requires a separate staff decision.');
                }
            }
            $query->update(['revoked_at' => $revoked ? now('UTC')->format('Y-m-d H:i:s.u') : null]);
            $this->audit->record($actor, $revoked ? 'legacy.revoked' : 'legacy.restored', 'succeeded', subjectAccount: $account->id, subjectType: 'legacy_link', subjectId: $link->id, decision: $decision);
        }, attempts: 3);
    }

    /** The identity pointer of a published source user record, created on first use (a principal carries no credential of its own). */
    public function ensurePrincipal(int $source, int $recordId): WebPrincipal
    {
        $existing = WebPrincipal::where('source_id', $source)->where('source_table_id', 117)->where('source_record_id', $recordId)->first();
        if ($existing !== null) {
            return $existing;
        }
        if ($recordId < 1 || ! in_array($source, app(SourceCatalog::class)->sourceIds(), true) || ! $this->identity->publishedUsers($source)->where('r.id', $recordId)->exists()) {
            throw new InvalidArgumentException('The source user is not a published historical account.');
        }
        $time = now('UTC')->format('Y-m-d H:i:s.u');
        $id = DB::table('web_principals')->insertGetId(['source_id' => $source, 'source_table_id' => 117, 'source_record_id' => $recordId, 'password_hash' => null, 'disabled_at' => null, 'created_at' => $time, 'updated_at' => $time]);

        return WebPrincipal::findOrFail($id);
    }

    /** Adds one reviewed historical identity to an existing account. A principal already linked to any account is never moved. */
    public function linkPrincipal(WebAccount $account, int $principalId, string $decision, ?int $actor = null): int
    {
        if (! AccountAuditTrail::validDecision($decision)) {
            throw new InvalidArgumentException('A link review decision is required.');
        }

        return DB::transaction(function () use ($account, $principalId, $decision, $actor) {
            WebAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $principal = WebPrincipal::whereKey($principalId)->lockForUpdate()->first();
            if ($principal === null || DB::table('account_legacy_links')->where('principal_id', $principalId)->exists()) {
                throw new InvalidArgumentException('A reviewed principal is missing or already linked.');
            }
            if ($this->identity->activeUser($principal) === null) {
                throw new InvalidArgumentException('Inactive legacy identity requires a separate staff decision.');
            }
            $linkId = DB::table('account_legacy_links')->insertGetId([
                'account_id' => $account->id, 'source_id' => $principal->source_id, 'principal_id' => $principal->id,
                'decision_locator' => $decision, 'reviewed_at' => now('UTC')->format('Y-m-d H:i:s.u'), 'revoked_at' => null,
            ]);
            $this->audit->record($actor, 'legacy.linked', 'succeeded', subjectAccount: $account->id, subjectType: 'legacy_link', subjectId: $linkId, decision: $decision);

            return (int) $linkId;
        }, attempts: 3);
    }

    /** Disables or re-enables the whole web account (every city); protected requests recheck it. */
    public function setDisabled(WebAccount $account, bool $disabled, ?int $actor = null, ?string $decision = null): void
    {
        if ($decision !== null && ! AccountAuditTrail::validDecision($decision)) {
            throw new InvalidArgumentException('Invalid decision reference.');
        }
        DB::transaction(function () use ($account, $disabled, $actor, $decision) {
            WebAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $time = now('UTC')->format('Y-m-d H:i:s.u');
            DB::table('web_accounts')->where('id', $account->id)->update(['disabled_at' => $disabled ? $time : null, 'updated_at' => $time]);
            $this->audit->record($actor, $disabled ? 'accounts.disabled' : 'accounts.enabled', 'succeeded', subjectAccount: $account->id, subjectType: 'account', subjectId: $account->id, decision: $decision);
        }, attempts: 3);
    }
}
