<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\Field\SpecialCaseService;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class ImportedDeletionTest extends TestCase
{
    public function test_hiding_an_imported_record_keeps_every_trace_and_is_reversible_over_http(): void
    {
        $this->withoutVite();
        ApplicationFixture::sqliteSchema();
        $account = ApplicationFixture::account();
        FieldFixture::grant($account);
        $intake = ApplicationFixture::intake(1);
        FieldFixture::specialTypes(1);
        $project = FinanceFixture::acceptedProject($account, $intake);
        $case = ApplicationFixture::row('specialcase', 1, ['c_Reason' => 'imported review', 'c_Date' => '2020-05-05 00:00:00'], ['MicroProject_ID' => $project]);
        FieldFixture::markImported($case, 'op_specialcase');
        $entity = (array) DB::table('operational_entities')->where('id', $case)->first();
        $origins = DB::table('operational_origins')->where('entity_id', $case)->count();
        $url = '/en/applications/'.$project.'/special-cases';

        $this->actingAs($account);
        $this->get($url)->assertOk()->assertSee('imported review')->assertDontSee('Deleted records');
        // The normal delete capability is not enough for an imported record: no delete button, and a forged request is refused.
        $this->get($url)->assertDontSee('Confirm deletion');
        $version = app(SpecialCaseService::class)->show($account, $project)['version'];
        $this->post($url.'/'.$case.'/delete', ['version' => $version])->assertForbidden();

        FieldFixture::grantImportedDelete($account);
        $this->get($url)->assertOk()->assertSee('Confirm deletion');
        $this->post($url.'/'.$case.'/delete', ['version' => $version])->assertRedirect();
        $this->get($url)->assertOk()->assertSee('Deleted records')->assertSee('Restore');

        // The record, its identity and its origin are untouched; only a hiding row exists.
        self::assertSame(1, DB::table('op_specialcase')->where('id', $case)->count());
        self::assertSame($entity, (array) DB::table('operational_entities')->where('id', $case)->first());
        self::assertSame($origins, DB::table('operational_origins')->where('entity_id', $case)->count());
        $deletion = DB::table('operational_deletions')->first();
        self::assertSame($account->id, (int) $deletion->deleted_by_account_id);
        self::assertSame($case, (int) $deletion->entity_id);
        self::assertSame(0, app(OperationalTables::class)->query('specialcase')->where('p.id', $case)->count());
        self::assertSame(1, app(OperationalTables::class)->query('specialcase', true)->where('p.id', $case)->count());

        $this->post($url.'/'.$case.'/restore')->assertRedirect();
        $this->get($url)->assertOk()->assertSee('imported review')->assertDontSee('Deleted records');
        self::assertSame(0, DB::table('operational_deletions')->count());

        DB::table('account_city_capabilities')->where('capability', 'imported.delete')->update(['revoked_at' => now()]);
        $this->post($url.'/'.$case.'/restore')->assertForbidden();
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'special_cases.deleted')->count());
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'special_cases.restored')->count());
    }

    public function test_the_data_entry_role_does_not_carry_the_administrator_only_capability(): void
    {
        self::assertNotContains('imported.delete', AccessRoles::capabilities('data-entry'));
        self::assertContains('imported.delete', AccountAccess::CAPABILITIES);
    }
}
