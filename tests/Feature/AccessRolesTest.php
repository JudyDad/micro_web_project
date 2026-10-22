<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AccessRoles;
use App\Services\AccountAccess;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class AccessRolesTest extends TestCase
{
    public function test_the_data_entry_role_grants_read_write_and_delete_per_city_and_is_audited(): void
    {
        ApplicationFixture::sqliteSchema();
        $account = ApplicationFixture::account();
        DB::table('account_city_capabilities')->where('account_id', $account->id)->delete();
        $roles = app(AccessRoles::class);
        $roles->assign($account, 1, 'data-entry', 'synthetic:roles');
        $roles->assign($account, 1, 'data-entry', 'synthetic:roles');
        $access = app(AccountAccess::class);
        foreach (AccessRoles::capabilities('data-entry') as $capability) {
            self::assertContains($capability, AccountAccess::CAPABILITIES);
            self::assertTrue($access->check($account, 1, $capability), $capability);
            self::assertFalse($access->check($account, 2, $capability), 'grants stay per city');
        }
        self::assertFalse($access->check($account, 1, 'finance.manage'));
        self::assertSame(count(AccessRoles::capabilities('data-entry')), DB::table('account_city_capabilities')->where('account_id', $account->id)->count());
        self::assertGreaterThanOrEqual(count(AccessRoles::capabilities('data-entry')), DB::table('account_audit_events')->where('event_type', 'capability.granted')->count());
        $this->expectException(\InvalidArgumentException::class);
        AccessRoles::capabilities('nobody');
    }
}
