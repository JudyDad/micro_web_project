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

    public function test_every_role_is_made_of_known_capabilities_and_matches_the_owner_decisions(): void
    {
        foreach (AccessRoles::ROLES as $role => $capabilities) {
            self::assertSame($capabilities, array_values(array_unique($capabilities)), $role.' lists a capability once');
            self::assertSame([], array_diff($capabilities, AccountAccess::CAPABILITIES), $role.' uses only known capabilities');
        }
        foreach (AccessRoles::RECIPIENTS as $group => $roles) {
            self::assertSame([], array_diff($roles, array_keys(AccessRoles::ROLES)), $group.' routes to defined roles');
        }
        self::assertSame(AccountAccess::CAPABILITIES, AccessRoles::capabilities('administrator'));
        self::assertContains('tasks.manage', AccessRoles::capabilities('media'), 'Media can have tasks on the task board');
        self::assertNotContains('tasks.view', AccessRoles::capabilities('guest'), 'Guest cannot');
        foreach (['attachments.download', 'finance.view', 'finance.manage', 'executive_files.manage', 'purchasing.manage'] as $capability) {
            self::assertContains($capability, AccessRoles::capabilities('financial'), 'Financial: '.$capability);
        }
        self::assertNotContains('finance.manage', AccessRoles::capabilities('legal'));
        self::assertContains('executive_files.manage', AccessRoles::capabilities('legal'));
        self::assertSame(['payment' => ['financial'], 'communication' => ['communication'], 'executive_file' => ['legal']], array_intersect_key(AccessRoles::RECIPIENTS, array_flip(['payment', 'communication', 'executive_file'])));
        self::assertNotContains('guest', AccessRoles::RECIPIENTS['state']);
    }

    public function test_assigning_a_role_records_it_for_notice_routing_and_removing_it_keeps_shared_capabilities(): void
    {
        ApplicationFixture::sqliteSchema();
        $account = ApplicationFixture::account();
        DB::table('account_city_capabilities')->where('account_id', $account->id)->delete();
        $roles = app(AccessRoles::class);
        $roles->assign($account, 1, 'financial', 'synthetic:roles');
        $roles->assign($account, 1, 'legal', 'synthetic:roles');
        $roles->assign($account, 1, 'legal', 'synthetic:roles');
        self::assertSame(['financial', 'legal'], $roles->roles($account, 1));
        self::assertSame([], $roles->roles($account, 2));
        self::assertSame([$account->id], $roles->holders(1, ['financial'])->all());
        self::assertSame([], $roles->holders(2, ['financial'])->all());
        self::assertSame(2, DB::table('account_city_roles')->count());

        $revoked = $roles->revoke($account, 1, 'financial', 'synthetic:roles');
        self::assertSame(count(AccessRoles::capabilities('financial')) - count(array_intersect(AccessRoles::capabilities('financial'), AccessRoles::capabilities('legal'))), $revoked);
        self::assertTrue(app(AccountAccess::class)->check($account, 1, 'finance.view'), 'the legal role still needs it');
        self::assertFalse(app(AccountAccess::class)->check($account, 1, 'finance.manage'));
        self::assertSame([], $roles->holders(1, ['financial'])->all());
        DB::table('web_accounts')->where('id', $account->id)->update(['disabled_at' => now()]);
        self::assertSame([], $roles->holders(1, ['legal'])->all(), 'a disabled account receives nothing');
        $this->expectException(\InvalidArgumentException::class);
        $roles->revoke($account, 1, 'financial', 'synthetic:roles');
    }
}
