<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Named bundles of the per-city capabilities (docs/planning/phase-5/staff-model-proposal.md). A role is assigned to an account in one city
 * by an administrator: its capabilities are granted (each stays individually audited and revocable) and the assignment is recorded in
 * `account_city_roles`, which is what routes notices to people (the financial role receives payment notices, and so on).
 */
final class AccessRoles
{
    private const READ = ['projects.view', 'people.view', 'applications.view'];

    private const TASKS = ['tasks.view', 'tasks.manage', 'notifications.view'];

    public const ROLES = [
        'administrator' => AccountAccess::CAPABILITIES,
        // Reads, writes and deletes the records around an application: people, applications, assessments, visits and special cases.
        'data-entry' => [
            'projects.view', 'people.view', 'people.manage', 'applications.view', 'applications.manage', 'attachments.download', 'assessments.view', 'assessments.manage',
            'special_cases.view', 'special_cases.manage', 'special_cases.delete', 'visits.view', 'visits.manage', 'visits.delete',
            'me_visits.view', 'me_visits.manage', 'me_visits.delete', 'profession_meetings.view', 'profession_meetings.manage', 'profession_meetings.delete',
            'tasks.view', 'notifications.view',
        ],
        'financial' => [
            ...self::READ, 'attachments.download', 'finance.view', 'finance.manage', 'executive_files.view', 'executive_files.manage', 'purchasing.view', 'purchasing.manage', ...self::TASKS,
        ],
        'legal' => [...self::READ, 'attachments.download', 'finance.view', 'executive_files.view', 'executive_files.manage', ...self::TASKS],
        'media' => [...self::READ, ...self::TASKS],
        'guest' => self::READ,
        // Duties the desktop kept as flags on a user (IsME, IsCommunication, IsCashier).
        'monitoring' => [
            'projects.view', 'applications.view', 'visits.view', 'visits.manage', 'me_visits.view', 'me_visits.manage', 'special_cases.view', 'special_cases.manage',
            'profession_meetings.view', 'profession_meetings.manage', ...self::TASKS,
        ],
        'communication' => ['projects.view', 'applications.view', ...self::TASKS],
        'cashier' => ['projects.view', 'applications.view', 'finance.view', 'notifications.view'],
    ];

    /** Which roles receive each kind of generated notice in a city. */
    public const RECIPIENTS = [
        'payment' => ['financial', 'cashier'],
        'communication' => ['communication'],
        'executive_file' => ['legal'],
        // Every role except Guest (the desktop excluded Guest and Out of Service users from state notices).
        'state' => ['administrator', 'data-entry', 'financial', 'legal', 'media', 'monitoring', 'communication', 'cashier'],
        'message' => ['administrator', 'data-entry', 'financial', 'legal', 'media', 'guest', 'monitoring', 'communication', 'cashier'],
    ];

    public function __construct(private AccountProvisioner $provisioner) {}

    /** @return list<string> */
    public static function capabilities(string $role): array
    {
        return self::ROLES[$role] ?? throw new InvalidArgumentException('Unknown role.');
    }

    /** Grants every capability of the role in one city and records the assignment; an already active assignment is refreshed, never duplicated. */
    public function assign(WebAccount $account, int $city, string $role, string $decision, ?int $actor = null): void
    {
        $capabilities = self::capabilities($role);
        DB::transaction(function () use ($account, $city, $role, $decision, $actor, $capabilities) {
            foreach ($capabilities as $capability) {
                $this->provisioner->grant($account, $city, $capability, $decision, $actor);
            }
            $time = now('UTC')->format('Y-m-d H:i:s.u');
            $row = DB::table('account_city_roles')->where('account_id', $account->id)->where('city_id', $city)->where('role', $role)->lockForUpdate()->first();
            if ($row === null) {
                DB::table('account_city_roles')->insert(['account_id' => $account->id, 'city_id' => $city, 'role' => $role, 'decision_locator' => $decision, 'assigned_at' => $time, 'revoked_at' => null]);
            } else {
                DB::table('account_city_roles')->where('id', $row->id)->update(['decision_locator' => $decision, 'assigned_at' => $time, 'revoked_at' => null]);
            }
        });
    }

    /** Ends the assignment and revokes the capabilities that no other active role of the account in that city still includes. */
    public function revoke(WebAccount $account, int $city, string $role, string $decision, ?int $actor = null): int
    {
        self::capabilities($role);

        return DB::transaction(function () use ($account, $city, $role, $decision, $actor) {
            $row = DB::table('account_city_roles')->where('account_id', $account->id)->where('city_id', $city)->where('role', $role)->whereNull('revoked_at')->lockForUpdate()->first();
            if ($row === null) {
                throw new InvalidArgumentException('The role is not assigned.');
            }
            DB::table('account_city_roles')->where('id', $row->id)->update(['revoked_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
            $kept = [];
            foreach ($this->roles($account, $city) as $other) {
                $kept = array_merge($kept, self::capabilities($other));
            }
            $active = DB::table('account_city_capabilities')->where('account_id', $account->id)->where('city_id', $city)->whereNull('revoked_at')->pluck('capability')->all();
            $revoke = array_values(array_diff(array_intersect(self::capabilities($role), $active), $kept));
            foreach ($revoke as $capability) {
                $this->provisioner->revoke($account, $city, $capability, $decision, $actor);
            }

            return count($revoke);
        });
    }

    /** @return list<string> the account's active roles in the city */
    public function roles(WebAccount $account, int $city): array
    {
        return DB::table('account_city_roles')->where('account_id', $account->id)->where('city_id', $city)->whereNull('revoked_at')->orderBy('role')->pluck('role')->all();
    }

    /**
     * Enabled accounts holding any of the roles in the city, one entry per account.
     *
     * @param  list<string>  $roles
     * @return Collection<int, int> account ids
     */
    public function holders(int $city, array $roles): Collection
    {
        return DB::table('account_city_roles as r')->join('web_accounts as a', 'a.id', '=', 'r.account_id')
            ->where('r.city_id', $city)->whereIn('r.role', $roles)->whereNull('r.revoked_at')->whereNull('a.disabled_at')
            ->orderBy('a.id')->pluck('a.id')->map(fn ($id) => (int) $id)->unique()->values();
    }
}
