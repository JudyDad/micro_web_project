<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\AccountProvisioner;
use App\Services\SourceIdentity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        private AccountAccess $access, private AccountAuditTrail $audit, private AccountProvisioner $provisioner, private SourceIdentity $identity,
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
        $account = $this->visible($managed)->where('a.id', $id)->first(['a.id', 'a.username', 'a.password_hash', 'a.credential_principal_id', 'a.disabled_at', 'a.created_at']);
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

    /** The account with what the administrator may change: per-city capabilities, reviewed identities and candidates to link. */
    public function show(WebAccount $admin, int $accountId): array
    {
        $managed = $this->managed($admin);
        $account = $this->account($admin, $accountId, $managed);
        $this->audit->record($admin->id, 'accounts.view', 'succeeded', subjectAccount: $accountId, subjectType: 'account', subjectId: $accountId);
        $cities = $this->cities($admin)->keyBy('id');
        $self = $accountId === $admin->id;
        $active = DB::table('account_city_capabilities')->where('account_id', $accountId)->whereIn('city_id', $managed)->whereNull('revoked_at')->get()->groupBy('city_id');
        $panels = [];
        foreach ($managed as $city) {
            $grantable = $this->grantable($admin, $city);
            $have = ($active->get($city) ?? collect())->pluck('capability')->all();
            $panels[] = [
                'city' => $cities->get($city), 'grantable' => $grantable, 'active' => $have, 'fixed' => array_values(array_diff($have, $grantable)),
                'roles' => collect(AccessRoles::ROLES)->filter(fn ($caps) => array_diff($caps, $grantable) === [])->keys()->all(),
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
            'account' => $account, 'self' => $self, 'panels' => $panels, 'links' => $links, 'otherLinks' => $otherLinks,
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

    /** Grants every capability of a named role in one city (each stays individually audited and revocable). */
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
        $account = WebAccount::findOrFail($accountId);
        $decision = $this->decision($admin);
        DB::transaction(function () use ($admin, $account, $city, $capabilities, $decision) {
            foreach ($capabilities as $capability) {
                $this->provisioner->grant($account, $city, $capability, $decision, $admin->id);
            }
        });

        return count($capabilities);
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
     * confirmed to be one person; `credential`: the identity whose password the account inherits; `roles`: optional role per city.
     *
     * @param  array{identities?: list<string>, credential?: ?string, username?: ?string, confirm?: mixed, roles?: array<int|string, ?string>}  $input
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
        foreach ($pairs as $key => [$source, $record]) {
            $cityOf[$key] = $this->cityOfSource($admin, $source, $managed);
            $user = $this->identity->publishedUsers($source)->where('r.id', $record)->first(['r.id', 'u.UserName', 'u.UserRoleID']);
            ($user !== null && (int) $user->UserRoleID !== self::OUT_OF_SERVICE) || $fail('identities', 'identity');
            DB::table('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')->where('wp.source_id', $source)->where('wp.source_record_id', $record)->doesntExist() || $fail('identities', 'identity');
            $names[$key] = (string) $user->UserName;
        }
        $username = (string) ($input['username'] ?? '');
        $username = $username === '' ? $names[$credential] : $username;
        (mb_strlen($username) <= 200 && strlen($username) <= 800 && $username === trim($username) && preg_match('//u', $username) === 1) || $fail('username', 'username');
        DB::table('web_accounts')->where('username', $username)->doesntExist() || $fail('username', 'username_taken');
        $capabilities = [];
        foreach (array_unique($cityOf) as $city) {
            $role = (string) (((array) ($input['roles'] ?? []))[$city] ?? '');
            if ($role === '') {
                continue;
            }
            $caps = AccessRoles::ROLES[$role] ?? $fail('roles', 'role');
            array_diff($caps, $this->grantable($admin, $city)) === [] || $fail('roles', 'role_beyond');
            $capabilities[$city] = $caps;
        }
        $decision = $this->decision($admin);

        try {
            return (int) DB::transaction(function () use ($admin, $pairs, $credential, $username, $capabilities, $decision) {
                $principals = [];
                foreach ($pairs as $key => [$source, $record]) {
                    $principals[$key] = (int) $this->provisioner->ensurePrincipal($source, $record)->id;
                }
                $account = $this->provisioner->provision($username, $principals[$credential], array_values($principals), $capabilities, $decision, $admin->id);

                return $account->id;
            });
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages(['identities' => __('admin.errors.identity')]);
        }
    }

    /**
     * The historical staff of the managed cities with their roles and flags, and the web account linked to each: who receives which notices.
     *
     * @param  array{city?: ?int}  $filters
     * @return array{cities: Collection, groups: list<array{city: object, users: Collection}>}
     */
    public function staff(WebAccount $admin, array $filters): array
    {
        $managed = $this->managed($admin);
        $cities = $this->cities($admin);
        $selected = ! empty($filters['city']) && in_array((int) $filters['city'], $managed, true) ? [(int) $filters['city']] : $managed;
        $groups = [];
        foreach ($selected as $city) {
            $users = collect();
            foreach (DB::table('sources')->where('city_id', $city)->pluck('id') as $source) {
                $rows = $this->identity->publishedUsers((int) $source)->orderBy('u.UserName')->orderBy('u.UserID')->get(['r.id', 'u.UserID', 'u.UserName', 'u.UserRoleID', 'u.IsVisitor', 'u.IsME', 'u.IsCommunication', 'u.IsCashier']);
                $links = $rows->isEmpty() ? collect() : DB::table('web_principals as wp')->join('account_legacy_links as l', 'l.principal_id', '=', 'wp.id')->join('web_accounts as a', 'a.id', '=', 'l.account_id')
                    ->where('wp.source_id', (int) $source)->whereIn('wp.source_record_id', $rows->pluck('id')->all())->get(['wp.source_record_id', 'a.id as account_id', 'a.username', 'l.revoked_at'])->keyBy('source_record_id');
                foreach ($rows as $row) {
                    $link = $links->get((int) $row->id);
                    $users->push((object) [
                        'UserID' => (int) $row->UserID, 'username' => (string) $row->UserName, 'role' => (int) $row->UserRoleID, 'flags' => $this->flags($row),
                        'account_id' => $link === null ? null : (int) $link->account_id, 'account' => $link === null ? null : (string) $link->username, 'revoked' => $link !== null && $link->revoked_at !== null,
                    ]);
                }
            }
            $groups[] = ['city' => $cities->firstWhere('id', $city), 'users' => $users];
        }
        $this->audit->record($admin->id, 'accounts.list', 'succeeded');

        return ['cities' => $cities, 'groups' => $groups];
    }
}
