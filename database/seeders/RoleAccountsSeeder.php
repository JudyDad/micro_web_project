<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * One test account per staff role, all with the password "password", for viewing the site as each role while no real
 * data is in use (owner instruction of 28 October 2026). Re-running always restores them: a missing account is created,
 * an existing one gets the password reset, re-enabled and its role re-assigned in every city. Refuses to run in production.
 *
 *   php artisan db:seed --class=RoleAccountsSeeder
 */
final class RoleAccountsSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public const DECISION = 'seed:test-role-accounts';

    /** username => role; `super.admin` is an extra administrator kept from the first local test round. */
    public static function accounts(): array
    {
        $accounts = ['super.admin' => 'administrator'];
        foreach (array_keys(AccessRoles::ROLES) as $role) {
            $accounts['role.'.$role] = $role;
        }

        return $accounts;
    }

    public function run(AccountProvisioner $provisioner, AccessRoles $roles): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Test role accounts are never seeded in production.');
        }
        $cities = DB::table('cities')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $now = now('UTC')->format('Y-m-d H:i:s.u');
        foreach (self::accounts() as $username => $role) {
            $account = WebAccount::where('username', $username)->first()
                ?? $provisioner->createPlain($username, null, Hash::make(self::PASSWORD), self::DECISION);
            // A normal password, not the one-time kind: password_changed_at set means no forced change at first login.
            DB::table('web_accounts')->where('id', $account->id)->update(['password_hash' => Hash::make(self::PASSWORD), 'password_changed_at' => $now, 'disabled_at' => null, 'updated_at' => $now]);
            foreach ($cities as $city) {
                $roles->assign($account->fresh(), $city, $role, self::DECISION);
            }
            $this->command?->info(sprintf('%s → %s in %d cities', $username, $role, count($cities)));
        }
    }
}
