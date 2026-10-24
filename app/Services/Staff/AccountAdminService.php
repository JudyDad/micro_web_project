<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\AccountPasswords;
use App\Services\AccountProvisioner;
use App\Services\Operational\OperationalTables;
use App\Services\SourceIdentity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Staff administration of web accounts, city by city. An administrator holds `accounts.manage` in some cities and acts only inside them:
 * it sees accounts that hold grants or reviewed links there (and accounts that have neither, so they can be assigned), grants and
 * revokes only the capabilities it holds itself, links historical identities of its own cities after review, and never edits its own
 * grants. Every change is written through {@see AccountProvisioner}, which records it in the account audit trail with the acting account.
 * A web account has no password set here: it inherits the credential of the reviewed historical identity it was created from.
 */
final class AccountAdminService
{
    public const PER_PAGE = 50;

    /** Historical role ids (`Notification_Box`): the web shows them as read-only context. */
    public const OUT_OF_SERVICE = 6;

    public function __construct(
        private AccountAccess $access, private AccountAuditTrail $audit, private AccountProvisioner $provisioner, private SourceIdentity $identity, private AccessRoles $roles,
        private OperationalTables $tables,
    ) {}

    /** Cities the administrator manages. */
    public function cities(WebAccount $admin): Collection
    {
        return $this->access->cities($admin, 'accounts.manage');
    }

    /** @return list<int> */
    private function managed(WebAccount $admin): array
    {
        $cities = $this->cities($admin);
        if ($cities->isEmpty()) {
            $this->audit->record($admin->id, 'access.denied', 'denied');
            abort(403);
        }

        return $cities->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function deny(WebAccount $admin): never
    {
        $this->audit->record($admin->id, 'access.denied', 'denied');
        abort(403);
    }

    /** Capabilities the administrator holds in a city: the only ones it may grant or revoke there. */
    private function grantable(WebAccount $admin, int $city): array
    {
        return array_values(array_intersect(AccountAccess::CAPABILITIES, DB::table('account_city_capabilities')->where('account_id', $admin->id)->where('city_id', $city)
            ->whereNull('revoked_at')->pluck('capability')->all()));
    }

    private function decision(WebAccount $admin): string
    {
        return 'web:admin:'.$admin->id.':'.now('UTC')->format('Ymd\THis');
    }

    /** The accounts an administrator may see: grants or links in its cities, or none at all. */
    private function visible(array $managed): Builder
    {
        $grants = fn (Builder $q) => $q->selectRaw('1')->from('account_city_capabilities as g')->whereColumn('g.account_id', 'a.id')->whereNull('g.revoked_at');
        $links = fn (Builder $q) => $q->selectRaw('1')->from('account_legacy_links as l')->whereColumn('l.account_id', 'a.id');

        return DB::table('web_accounts as a')->where(function (Builder $v) use ($managed, $grants, $links) {
            $v->whereExists(fn (Builder $q) => $grants($q)->whereIn('g.city_id', $managed))
                ->orWhereExists(fn (Builder $q) => $links($q)->join('sources as s', 's.id', '=', 'l.source_id')->whereIn('s.city_id', $managed))
                ->orWhere(fn (Builder $n) => $n->whereNotExists(fn (Builder $q) => $grants($q))->whereNotExists(fn (Builder $q) => $links($q)));
        });
    }

    private function account(WebAccount $admin, int $id, array $managed): object
    {
        $account = $this->visible($managed)->where('a.id', $id)->first(['a.id', 'a.username', 'a.email', 'a.password_hash', 'a.credential_principal_id', 'a.disabled_at', 'a.created_at']);
        abort_if($account === null, 404);

        return $account;
    }

    /**
     * @param  array{q?: ?string, city?: ?int, status?: ?string}  $filters
     * @return array{rows: LengthAwarePaginator, cities: Collection, grantedCities: array<int, array<int, int>>}
     */
    public function index(WebAccount $admin, array $filters): array
    {
        $managed = $this->managed($admin);
        $cities = $this->cities($admin);
        $this->audit->record($admin->id, 'accounts.list', 'succeeded');
        $query = $this->visible($managed);
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->whereRaw("a.username LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%']);
        }
        if (in_array($filters['status'] ?? '', ['active', 'disabled'], true)) {
            $filters['status'] === 'disabled' ? $query->whereNotNull('a.disabled_at') : $query->whereNull('a.disabled_at');
        }
        if (! empty($filters['city']) && in_array((int) $filters['city'], $managed, true)) {
            $city = (int) $filters['city'];
            $query->where(function (Builder $c) use ($city) {
                $c->whereExists(fn (Builder $q) => $q->selectRaw('1')->from('account_city_capabilities as g2')->whereColumn('g2.account_id', 'a.id')->whereNull('g2.revoked_at')->where('g2.city_id', $city))
                    ->orWhereExists(fn (Builder $q) => $q->selectRaw('1')->from('account_legacy_links as l2')->join('sources as s2', 's2.id', '=', 'l2.source_id')->whereColumn('l2.account_id', 'a.id')->where('s2.city_id', $city));
            });
        }
        $rows = $query->orderBy('a.username')->orderBy('a.id')->paginate(self::PER_PAGE, ['a.id', 'a.username', 'a.disabled_at', 'a.created_at'])->withQueryString();
        $ids = $rows->getCollection()->pluck('id')->all();
        $granted = [];
        $linked = [];
        if ($ids !== []) {
            foreach (DB::table('account_city_capabilities')->whereIn('account_id', $ids)->whereIn('city_id', $managed)->whereNull('revoked_at')->selectRaw('account_id, city_id, COUNT(*) as n')->groupBy('account_id', 'city_id')->get() as $row) {
                $granted[(int) $row->account_id][(int) $row->city_id] = (int) $row->n;
            }
            foreach (DB::table('account_legacy_links as l')->join('sources as s', 's.id', '=', 'l.source_id')->whereIn('l.account_id', $ids)->whereIn('s.city_id', $managed)->whereNull('l.revoked_at')
                ->selectRaw('l.account_id, COUNT(*) as n')->groupBy('l.account_id')->get() as $row) {
                $linked[(int) $row->account_id] = (int) $row->n;
            }
        }
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($granted, $linked, $admin) {
            $row->username = (string) $row->username;
            $row->grants = $granted[(int) $row->id] ?? [];
            $row->links = $linked[(int) $row->id] ?? 0;
            $row->self = (int) $row->id === $admin->id;

            return $row;
        }));

        return ['rows' => $rows, 'cities' => $cities, 'grantedCities' => $granted];
    }

    /** The account with what the administrator may change: per-city roles and capabilities, e-mail, reviewed identities and candidates to link. */
    public function show(WebAccount $admin, int $accountId): array
    {
        $managed = $this->managed($admin);
        $account = $this->account($admin, $accountId, $managed);
        $this->audit->record($admin->id, 'accounts.view', 'succeeded', subjectAccount: $accountId, subjectType: 'account', subjectId: $accountId);
        $cities = $this->cities($admin)->keyBy('id');
        $self = $accountId === $admin->id;
        $active = DB::table('account_city_capabilities')->where('account_id', $accountId)->whereIn('city_id', $managed)->whereNull('revoked_at')->get()->groupBy('city_id');
        $heldRoles = DB::table('account_city_roles')->where('account_id', $accountId)->whereIn('city_id', $managed)->whereNull('revoked_at')->orderBy('role')->get()->groupBy('city_id');
        $panels = [];
        foreach ($managed as $city) {
            $grantable = $this->grantable($admin, $city);
            $have = ($active->get($city) ?? collect())->pluck('capability')->all();
            $panels[] = [
                'city' => $cities->get($city), 'grantable' => $grantable, 'active' => $have, 'fixed' => array_values(array_diff($have, $grantable)),
                'roles' => collect(AccessRoles::ROLES)->filter(fn ($caps) => array_diff($caps, $grantable) === [])->keys()->all(),
                'held' => ($heldRoles->get($city) ?? collect())->pluck('role')->all(),
            ];
        }
        $links = DB::table('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')->join('sources as s', 's.id', '=', 'l.source_id')
            ->where('l.account_id', $accountId)->whereIn('s.city_id', $managed)->orderBy('l.id')->get(['l.id', 'l.source_id', 'l.principal_id', 'l.reviewed_at', 'l.revoked_at', 's.city_id', 'wp.source_record_id', 'wp.disabled_at as principal_disabled_at']);
        $users = [];
        foreach ($links->groupBy('source_id') as $source => $group) {
            $users[(int) $source] = $this->identity->publishedUsers((int) $source)->whereIn('r.id', $group->pluck('source_record_id')->all())->get(['r.id', 'u.UserName', 'u.UserRoleID'])->keyBy('id');
        }
        $links = $links->map(function ($link) use ($users, $account, $cities) {
            $user = $users[(int) $link->source_id]->get((int) $link->source_record_id);
            $link->city = $cities->get((int) $link->city_id);
            $link->username = $user === null ? null : (string) $user->UserName;
            $link->role = $user === null ? null : (int) $user->UserRoleID;
            $link->credential = (int) $account->credential_principal_id === (int) $link->principal_id && $account->password_hash === null;

            return $link;
        });
        $names = $links->pluck('username')->filter()->push($account->username)->map(fn ($name) => (string) $name)->all();
        $otherLinks = DB::table('account_legacy_links as l')->join('sources as s', 's.id', '=', 'l.source_id')->where('l.account_id', $accountId)->whereNotIn('s.city_id', $managed)->whereNull('l.revoked_at')->count();
        $account->username = (string) $account->username;
        $account->has_password = $account->password_hash !== null;
        unset($account->password_hash);

        return [
            'account' => $account, 'self' => $self, 'panels' => $panels, 'links' => $links, 'otherLinks' => $otherLinks, 'canEmail' => ! $self && $this->outranks($admin, $accountId, $managed),
            'candidates' => $self ? collect() : $this->unlinked($managed, $cities)->map(function ($user) use ($names) {
                $user->same = in_array($user->username, $names, true);

                return $user;
            })->sortBy([['same', 'desc'], ['username', 'asc']])->values(),
            'canDisable' => ! $self && $this->disablable($admin, $accountId, $managed),
        ];
    }

    /**
     * Sets one city's capabilities to exactly the submitted list, within what the administrator holds. Capabilities it does not hold are left
     * untouched. Returns the number granted and revoked.
     *
     * @param  list<string>  $capabilities
     * @return array{granted: int, revoked: int}
     */
    public function saveCapabilities(WebAccount $admin, int $accountId, int $city, array $capabilities): array
    {
        $managed = $this->managed($admin);
        in_array($city, $managed, true) || $this->deny($admin);
        $this->account($admin, $accountId, $managed);
        $accountId !== $admin->id || $this->deny($admin);
        $grantable = $this->grantable($admin, $city);
        $submitted = array_values(array_unique($capabilities));
        if (array_diff($submitted, $grantable) !== []) {
            throw ValidationException::withMessages(['capabilities' => __('admin.errors.capability')]);
        }
        $decision = $this->decision($admin);
        $account = WebAccount::findOrFail($accountId);

        return DB::transaction(function () use ($admin, $account, $city, $submitted, $grantable, $decision) {
            $active = DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', $city)->whereNull('revoked_at')->pluck('capability')->all();
            $grant = array_values(array_diff($submitted, $active));
            $revoke = array_values(array_diff(array_intersect($active, $grantable), $submitted));
            foreach ($grant as $capability) {
                $this->provisioner->grant($account, $city, $capability, $decision, $admin->id);
            }
            foreach ($revoke as $capability) {
                $this->provisioner->revoke($account, $city, $capability, $decision, $admin->id);
            }

            return ['granted' => count($grant), 'revoked' => count($revoke)];
        });
    }

    /** Assigns a named role in one city: its capabilities are granted (each still audited) and the assignment routes the city's notices to the account. */
    public function assignRole(WebAccount $admin, int $accountId, int $city, string $role): int
    {
        $managed = $this->managed($admin);
        in_array($city, $managed, true) || $this->deny($admin);
        $this->account($admin, $accountId, $managed);
        $accountId !== $admin->id || $this->deny($admin);
        $capabilities = AccessRoles::ROLES[$role] ?? throw ValidationException::withMessages(['role' => __('admin.errors.role')]);
        if (array_diff($capabilities, $this->grantable($admin, $city)) !== []) {
            throw ValidationException::withMessages(['role' => __('admin.errors.role_beyond')]);
        }
        $decision = $this->decision($admin);
        $this->roles->assign(WebAccount::findOrFail($accountId), $city, $role, $decision, $admin->id);
        $this->audit->record($admin->id, 'accounts.role_assigned', 'succeeded', $city, $accountId, 'account', $accountId, $decision);

        return count($capabilities);
    }

    /** Ends a role in one city and revokes the capabilities no other role of the account there still includes. Returns how many were revoked. */
    public function revokeRole(WebAccount $admin, int $accountId, int $city, string $role): int
    {
        $managed = $this->managed($admin);
        in_array($city, $managed, true) || $this->deny($admin);
        $this->account($admin, $accountId, $managed);
        $accountId !== $admin->id || $this->deny($admin);
        $capabilities = AccessRoles::ROLES[$role] ?? throw ValidationException::withMessages(['role' => __('admin.errors.role')]);
        if (array_diff($capabilities, $this->grantable($admin, $city)) !== []) {
            throw ValidationException::withMessages(['role' => __('admin.errors.role_beyond')]);
        }
        $decision = $this->decision($admin);
        try {
            $revoked = $this->roles->revoke(WebAccount::findOrFail($accountId), $city, $role, $decision, $admin->id);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages(['role' => __('admin.errors.role_not_held')]);
        }
        $this->audit->record($admin->id, 'accounts.role_revoked', 'succeeded', $city, $accountId, 'account', $accountId, $decision);

        return $revoked;
    }

    /** Links one more reviewed historical identity of a managed city to an existing account. */
    public function link(WebAccount $admin, int $accountId, int $source, int $record): void
    {
        $managed = $this->managed($admin);
        $this->cityOfSource($admin, $source, $managed);
        $this->account($admin, $accountId, $managed);
        $user = $this->identity->publishedUsers($source)->where('r.id', $record)->first(['r.id', 'u.UserRoleID']);
        if ($user === null || (int) $user->UserRoleID === self::OUT_OF_SERVICE) {
            throw ValidationException::withMessages(['identity' => __('admin.errors.identity')]);
        }
        $account = WebAccount::findOrFail($accountId);
        try {
            DB::transaction(function () use ($admin, $account, $source, $record) {
                $principal = $this->provisioner->ensurePrincipal($source, $record);
                $this->provisioner->linkPrincipal($account, (int) $principal->id, $this->decision($admin), $admin->id);
            });
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages(['identity' => __('admin.errors.identity')]);
        }
    }

    /** Revokes or restores a reviewed link. Revoking the credential of an account that has no password of its own is refused (lockout). */
    public function setLinkRevoked(WebAccount $admin, int $accountId, int $linkId, bool $revoked): void
    {
        $managed = $this->managed($admin);
        $account = $this->account($admin, $accountId, $managed);
        $link = DB::table('account_legacy_links')->where('id', $linkId)->where('account_id', $accountId)->first();
        abort_if($link === null, 404);
        $this->cityOfSource($admin, (int) $link->source_id, $managed);
        if ($revoked && $account->password_hash === null && (int) $account->credential_principal_id === (int) $link->principal_id) {
            throw ValidationException::withMessages(['link' => __('admin.errors.credential_link')]);
        }
        try {
            $this->provisioner->setLinkRevoked(WebAccount::findOrFail($accountId), (int) $link->principal_id, $revoked, $this->decision($admin), $admin->id);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages(['link' => __('admin.errors.identity')]);
        }
    }

    /** Disables or re-enables the whole account. Needs every city the account is involved in, and never one's own account. */
    public function setDisabled(WebAccount $admin, int $accountId, bool $disabled): void
    {
        $managed = $this->managed($admin);
        $this->account($admin, $accountId, $managed);
        if ($accountId === $admin->id || ! $this->disablable($admin, $accountId, $managed)) {
            $this->deny($admin);
        }
        $this->provisioner->setDisabled(WebAccount::findOrFail($accountId), $disabled, $admin->id, $this->decision($admin));
    }

    /** Whether every city the account is active in (grants or links) is managed by the administrator. */
    private function disablable(WebAccount $admin, int $accountId, array $managed): bool
    {
        $cities = DB::table('account_city_capabilities')->where('account_id', $accountId)->whereNull('revoked_at')->pluck('city_id')
            ->merge(DB::table('account_legacy_links as l')->join('sources as s', 's.id', '=', 'l.source_id')->where('l.account_id', $accountId)->whereNull('l.revoked_at')->pluck('s.city_id'))
            ->map(fn ($id) => (int) $id)->unique();

        return $cities->diff($managed)->isEmpty();
    }

    private function cityOfSource(WebAccount $admin, int $source, array $managed): int
    {
        $city = DB::table('sources')->where('id', $source)->value('city_id');
        if ($city === null || ! in_array((int) $city, $managed, true)) {
            $this->deny($admin);
        }

        return (int) $city;
    }

    /**
     * Published historical users of the managed cities that no account is linked to yet, for linking and for the duplicate review.
     *
     * @return Collection<int, object{source: int, city: int, city_name: ?object, record: int, UserID: int, username: string, role: int, flags: list<string>}>
     */
    private function unlinked(array $managed, Collection $cities): Collection
    {
        $result = collect();
        foreach (DB::table('sources')->whereIn('city_id', $managed)->orderBy('id')->get(['id', 'city_id']) as $source) {
            $users = $this->identity->publishedUsers((int) $source->id)->orderBy('u.UserName')->orderBy('u.UserID')->get(['r.id', 'u.UserID', 'u.UserName', 'u.UserRoleID', 'u.IsVisitor', 'u.IsME', 'u.IsCommunication', 'u.IsCashier']);
            if ($users->isEmpty()) {
                continue;
            }
            $linked = DB::table('web_principals as wp')->join('account_legacy_links as l', 'l.principal_id', '=', 'wp.id')->where('wp.source_id', $source->id)
                ->whereIn('wp.source_record_id', $users->pluck('id')->all())->pluck('wp.source_record_id')->map(fn ($id) => (int) $id)->all();
            foreach ($users as $user) {
                if (in_array((int) $user->id, $linked, true) || (int) $user->UserRoleID === self::OUT_OF_SERVICE) {
                    continue;
                }
                $result->push((object) [
                    'source' => (int) $source->id, 'city' => (int) $source->city_id, 'city_name' => $cities->get((int) $source->city_id), 'record' => (int) $user->id, 'UserID' => (int) $user->UserID,
                    'username' => (string) $user->UserName, 'role' => (int) $user->UserRoleID, 'flags' => $this->flags($user),
                ]);
            }
        }

        return $result;
    }

    /** @return list<string> */
    private function flags(object $user): array
    {
        return array_keys(array_filter(['visitor' => (int) $user->IsVisitor === 1, 'me' => (int) $user->IsME === 1, 'communication' => (int) $user->IsCommunication === 1, 'cashier' => (int) $user->IsCashier === 1]));
    }

    /** Unlinked identities grouped by exact username: the duplicate review before an account is created. */
    public function candidates(WebAccount $admin): Collection
    {
        $managed = $this->managed($admin);

        return $this->unlinked($managed, $this->cities($admin)->keyBy('id'))->groupBy('username')->map(fn ($group, $username) => (object) ['username' => (string) $username, 'identities' => $group->values()])->sortKeys()->values();
    }

    /**
     * Creates an account from reviewed historical identities of the managed cities. `identities`: "source:record" strings the administrator
     * confirmed to be one person; `credential`: the identity whose password the account inherits; `roles`: optional role per city; `email`:
     * optional (otherwise the credential identity's e-mail when it is valid and unused).
     *
     * @param  array{identities?: list<string>, credential?: ?string, username?: ?string, email?: ?string, confirm?: mixed, roles?: array<int|string, ?string>}  $input
     */
    public function create(WebAccount $admin, array $input): int
    {
        $managed = $this->managed($admin);
        $fail = fn (string $field, string $key): never => throw ValidationException::withMessages([$field => __('admin.errors.'.$key)]);
        ! empty($input['confirm']) || $fail('confirm', 'confirm');
        $pairs = [];
        foreach (array_unique((array) ($input['identities'] ?? [])) as $value) {
            preg_match('/\A([1-9][0-9]{0,9}):([1-9][0-9]{0,18})\z/', (string) $value, $m) === 1 || $fail('identities', 'identity');
            $pairs[(string) $value] = [(int) $m[1], (int) $m[2]];
        }
        $pairs !== [] || $fail('identities', 'identity');
        $credential = (string) ($input['credential'] ?? '');
        isset($pairs[$credential]) || $fail('credential', 'credential');
        $cityOf = [];
        $names = [];
        $emails = [];
        foreach ($pairs as $key => [$source, $record]) {
            $cityOf[$key] = $this->cityOfSource($admin, $source, $managed);
            $user = $this->identity->publishedUsers($source)->where('r.id', $record)->first(['r.id', 'u.UserName', 'u.UserRoleID', 'u.Email']);
            ($user !== null && (int) $user->UserRoleID !== self::OUT_OF_SERVICE) || $fail('identities', 'identity');
            DB::table('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')->where('wp.source_id', $source)->where('wp.source_record_id', $record)->doesntExist() || $fail('identities', 'identity');
            $names[$key] = (string) $user->UserName;
            $emails[$key] = (string) ($user->Email ?? '');
        }
        $username = (string) ($input['username'] ?? '');
        $username = $username === '' ? $names[$credential] : $username;
        (mb_strlen($username) <= 200 && strlen($username) <= 800 && $username === trim($username) && preg_match('//u', $username) === 1) || $fail('username', 'username');
        DB::table('web_accounts')->where('username', $username)->doesntExist() || $fail('username', 'username_taken');
        $typed = trim((string) ($input['email'] ?? ''));
        if ($typed !== '') {
            $email = $this->normalizeEmail($typed) ?? $fail('email', 'email');
            DB::table('web_accounts')->where('email', $email)->doesntExist() || $fail('email', 'email_taken');
        } else {
            $email = $this->normalizeEmail($emails[$credential]);
            $email !== null && DB::table('web_accounts')->where('email', $email)->exists() && $email = null;
        }
        $assign = [];
        foreach (array_unique($cityOf) as $city) {
            $role = (string) (((array) ($input['roles'] ?? []))[$city] ?? '');
            if ($role === '') {
                continue;
            }
            $caps = AccessRoles::ROLES[$role] ?? $fail('roles', 'role');
            array_diff($caps, $this->grantable($admin, $city)) === [] || $fail('roles', 'role_beyond');
            $assign[$city] = $role;
        }
        $decision = $this->decision($admin);

        try {
            return (int) DB::transaction(function () use ($admin, $pairs, $credential, $username, $email, $assign, $decision) {
                $principals = [];
                foreach ($pairs as $key => [$source, $record]) {
                    $principals[$key] = (int) $this->provisioner->ensurePrincipal($source, $record)->id;
                }
                $account = $this->provisioner->provision($username, $principals[$credential], array_values($principals), [], $decision, $admin->id);
                if ($email !== null) {
                    DB::table('web_accounts')->where('id', $account->id)->update(['email' => $email]);
                }
                foreach ($assign as $city => $role) {
                    $this->roles->assign($account, $city, $role, $decision, $admin->id);
                }

                return $account->id;
            });
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages(['identities' => __('admin.errors.identity')]);
        }
    }

    /**
     * Creates an account that has no desktop identity: the administrator gives a user name, an e-mail (used by "forgot password") and a role
     * for at least one managed city, and hands over the generated one-time password, shown once; the user must replace it at first login.
     *
     * @param  array{username?: ?string, email?: ?string, roles?: array<int|string, ?string>}  $input
     * @return array{id: int, password: string}
     */
    public function createPlain(WebAccount $admin, array $input): array
    {
        $managed = $this->managed($admin);
        $fail = fn (string $field, string $key): never => throw ValidationException::withMessages([$field => __('admin.errors.'.$key)]);
        $username = (string) ($input['username'] ?? '');
        ($username !== '' && mb_strlen($username) <= 200 && strlen($username) <= 800 && $username === trim($username) && preg_match('//u', $username) === 1) || $fail('username', 'username');
        DB::table('web_accounts')->where('username', $username)->doesntExist() || $fail('username', 'username_taken');
        $email = $this->normalizeEmail((string) ($input['email'] ?? '')) ?? $fail('email', 'email');
        DB::table('web_accounts')->where('email', $email)->doesntExist() || $fail('email', 'email_taken');
        $assign = [];
        foreach ((array) ($input['roles'] ?? []) as $city => $role) {
            $role = (string) $role;
            if ($role === '') {
                continue;
            }
            in_array((int) $city, $managed, true) || $this->deny($admin);
            $caps = AccessRoles::ROLES[$role] ?? $fail('roles', 'role');
            array_diff($caps, $this->grantable($admin, (int) $city)) === [] || $fail('roles', 'role_beyond');
            $assign[(int) $city] = $role;
        }
        $assign !== [] || $fail('roles', 'role_needed');
        $password = AccountPasswords::generate();
        $decision = $this->decision($admin);

        return DB::transaction(function () use ($admin, $username, $email, $password, $assign, $decision) {
            $account = $this->provisioner->createPlain($username, $email, Hash::make($password), $decision, $admin->id);
            foreach ($assign as $city => $role) {
                $this->roles->assign($account, $city, $role, $decision, $admin->id);
            }

            return ['id' => (int) $account->id, 'password' => $password];
        });
    }

    /** Sets or clears the e-mail "forgot password" checks. Only for accounts whose every capability the administrator also holds. */
    public function setEmail(WebAccount $admin, int $accountId, ?string $email): void
    {
        $managed = $this->managed($admin);
        $this->account($admin, $accountId, $managed);
        if ($accountId === $admin->id || ! $this->outranks($admin, $accountId, $managed)) {
            $this->deny($admin);
        }
        $value = trim((string) $email) === '' ? null : ($this->normalizeEmail((string) $email) ?? throw ValidationException::withMessages(['email' => __('admin.errors.email')]));
        if ($value !== null && DB::table('web_accounts')->where('email', $value)->where('id', '<>', $accountId)->exists()) {
            throw ValidationException::withMessages(['email' => __('admin.errors.email_taken')]);
        }
        DB::table('web_accounts')->where('id', $accountId)->update(['email' => $value, 'updated_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
        $this->audit->record($admin->id, 'accounts.email_set', 'succeeded', subjectAccount: $accountId, subjectType: 'account', subjectId: $accountId);
    }

    /** A lower-cased valid e-mail address of at most 190 characters, or null. */
    private function normalizeEmail(string $value): ?string
    {
        $value = mb_strtolower(trim($value));

        return $value !== '' && mb_strlen($value) <= 190 && filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? $value : null;
    }

    /** Whether every capability the account holds, in every city, is one the administrator holds there too (so it cannot take the account over). */
    private function outranks(WebAccount $admin, int $accountId, array $managed): bool
    {
        $held = DB::table('account_city_capabilities')->where('account_id', $accountId)->whereNull('revoked_at')->get()->groupBy('city_id');
        foreach ($held as $city => $rows) {
            if (! in_array((int) $city, $managed, true) || array_diff($rows->pluck('capability')->all(), $this->grantable($admin, (int) $city)) !== []) {
                return false;
            }
        }

        return true;
    }

    public function staff(WebAccount $admin, array $filters): array
    {
        $managed = $this->managed($admin);
        $cities = $this->cities($admin);
        $selected = ! empty($filters['city']) && in_array((int) $filters['city'], $managed, true) ? [(int) $filters['city']] : $managed;
        $groups = [];
        foreach ($selected as $city) {
            $accounts = DB::table('web_accounts as a')->where(function (Builder $q) use ($city) {
                $q->whereExists(fn (Builder $r) => $r->selectRaw('1')->from('account_city_roles as r')->whereColumn('r.account_id', 'a.id')->where('r.city_id', $city)->whereNull('r.revoked_at'))
                    ->orWhereExists(fn (Builder $g) => $g->selectRaw('1')->from('account_city_capabilities as g')->whereColumn('g.account_id', 'a.id')->where('g.city_id', $city)->whereNull('g.revoked_at'));
            })->orderBy('a.username')->get(['a.id', 'a.username', 'a.email', 'a.disabled_at']);
            $roles = DB::table('account_city_roles')->where('city_id', $city)->whereNull('revoked_at')->whereIn('account_id', $accounts->pluck('id')->all())->orderBy('role')->get()->groupBy('account_id');
            $tasks = DB::table('account_city_tasks')->where('city_id', $city)->whereNull('revoked_at')->whereIn('account_id', $accounts->pluck('id')->all())->selectRaw('account_id, COUNT(*) as n')->groupBy('account_id')->pluck('n', 'account_id');
            $groups[] = ['city' => $cities->firstWhere('id', $city), 'users' => $accounts->map(fn ($row) => (object) [
                'id' => (int) $row->id, 'username' => (string) $row->username, 'email' => $row->email, 'disabled' => $row->disabled_at !== null,
                'roles' => ($roles->get($row->id) ?? collect())->pluck('role')->all(), 'tasks' => (int) ($tasks[$row->id] ?? 0),
            ])];
        }
        $this->audit->record($admin->id, 'accounts.list', 'succeeded');

        return ['cities' => $cities, 'groups' => $groups];
    }

    /**
     * The task responsibility matrix of one city: the board's tasks, the enabled accounts that may manage tasks there and who is
     * responsible for each task now.
     *
     * @return array{city: object, cities: Collection, tasks: list<object>, accounts: Collection}
     */
    public function taskMatrix(WebAccount $admin, int $city): array
    {
        $managed = $this->managed($admin);
        in_array($city, $managed, true) || $this->deny($admin);
        $cities = $this->cities($admin);
        $current = DB::table('account_city_tasks')->where('city_id', $city)->whereNull('revoked_at')->get()->groupBy('task_id');
        $tasks = $this->boardTasks($city)->map(function ($task) use ($current) {
            $task->accounts = ($current->get($task->id) ?? collect())->pluck('account_id')->map(fn ($id) => (int) $id)->all();

            return $task;
        })->values()->all();
        $this->audit->record($admin->id, 'accounts.list', 'succeeded');

        return ['city' => $cities->firstWhere('id', $city), 'cities' => $cities, 'tasks' => $tasks, 'accounts' => $this->taskAccounts($city)];
    }

    /**
     * Sets who is responsible for each task of the city to exactly the submitted lists (task id → account ids). Returns how many
     * assignments were added and withdrawn.
     *
     * @param  array<int|string, list<int|string>>  $assign
     * @return array{added: int, withdrawn: int}
     */
    public function saveTasks(WebAccount $admin, int $city, array $assign): array
    {
        $managed = $this->managed($admin);
        in_array($city, $managed, true) || $this->deny($admin);
        $tasks = $this->boardTasks($city)->keyBy('id');
        $accounts = $this->taskAccounts($city)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $source = (int) $this->tables->source($city);
        $desired = [];
        foreach ($tasks->keys() as $taskId) {
            $desired[(int) $taskId] = array_values(array_unique(array_map('intval', (array) ($assign[$taskId] ?? []))));
        }
        foreach ($assign as $taskId => $list) {
            $tasks->has((int) $taskId) || throw ValidationException::withMessages(['tasks' => __('admin.errors.task')]);
            array_diff(array_map('intval', (array) $list), $accounts) === [] || throw ValidationException::withMessages(['tasks' => __('admin.errors.task_account')]);
        }

        return DB::transaction(function () use ($admin, $city, $source, $desired) {
            $time = now('UTC')->format('Y-m-d H:i:s.u');
            $added = 0;
            $withdrawn = 0;
            foreach ($desired as $taskId => $accountIds) {
                $rows = DB::table('account_city_tasks')->where('city_id', $city)->where('task_id', $taskId)->lockForUpdate()->get()->keyBy('account_id');
                foreach ($accountIds as $accountId) {
                    $row = $rows->get($accountId);
                    if ($row === null) {
                        DB::table('account_city_tasks')->insert(['account_id' => $accountId, 'city_id' => $city, 'source_id' => $source, 'task_id' => $taskId, 'assigned_at' => $time, 'revoked_at' => null]);
                        $added++;
                    } elseif ($row->revoked_at !== null) {
                        DB::table('account_city_tasks')->where('id', $row->id)->update(['assigned_at' => $time, 'revoked_at' => null]);
                        $added++;
                    }
                }
                foreach ($rows as $accountId => $row) {
                    if ($row->revoked_at === null && ! in_array((int) $accountId, $accountIds, true)) {
                        DB::table('account_city_tasks')->where('id', $row->id)->update(['revoked_at' => $time]);
                        $withdrawn++;
                    }
                }
            }
            $this->audit->record($admin->id, 'accounts.tasks_saved', 'succeeded', $city);

            return ['added' => $added, 'withdrawn' => $withdrawn];
        });
    }

    /** The board's tasks in the city's catalog, by name. */
    private function boardTasks(int $city): Collection
    {
        $source = (int) $this->tables->source($city);
        $legacy = array_keys(TaskBoardColumns::FUNDED + TaskBoardColumns::APPLICATION);

        return $this->tables->catalog('task', $city, $source)->filter(fn ($task) => in_array((int) ($task->c_T_ID ?? 0), $legacy, true))
            ->sortBy(fn ($task) => ($task->c_T_Type ?? '').sprintf('%05d', (int) $task->c_T_ID))->map(fn ($task) => (object) [
                'id' => (int) $task->id, 'legacy' => (int) $task->c_T_ID, 'name' => (string) ($task->c_T_Name ?? ''), 'type' => (string) ($task->c_T_Type ?? ''),
            ])->values();
    }

    /** Enabled accounts that hold `tasks.manage` in the city: the only ones a task can be assigned to. */
    private function taskAccounts(int $city): Collection
    {
        return DB::table('web_accounts as a')->join('account_city_capabilities as g', 'g.account_id', '=', 'a.id')->where('g.city_id', $city)->where('g.capability', 'tasks.manage')
            ->whereNull('g.revoked_at')->whereNull('a.disabled_at')->orderBy('a.username')->get(['a.id', 'a.username'])
            ->map(fn ($row) => (object) ['id' => (int) $row->id, 'username' => (string) $row->username])->values();
    }
}
