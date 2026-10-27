<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Operational\OperationalTables;
use App\Services\Reports\LookupService;
use Illuminate\Support\Facades\DB;
use Tests\Support\ApplicationFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

/** P6.2: the exports of notifications, the audit log, the lookup tables and the details grids, with the authorization and audit of their pages. */
final class OtherExportsTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        app()->setLocale('en');
        $this->account = ApplicationFixture::account();
        StaffFixture::grant($this->account);
        foreach ([1, 2] as $city) {
            DB::table('account_city_capabilities')->insert(['account_id' => $this->account->id, 'city_id' => $city, 'capability' => 'accounts.manage', 'decision_locator' => 'synthetic:p62', 'granted_at' => now('UTC')]);
        }
        config(['session.block_store' => 'array']);
        $this->intake = ApplicationFixture::intake(1);
        $this->project = FinanceFixture::acceptedProject($this->account, $this->intake);
        DB::table('web_notices')->delete();
        DB::table('account_audit_events')->delete();
        $this->actingAs($this->account);
    }

    /** @return array{0: array<int, string>, 1: list<list<string>>} */
    private function csv(string $url): array
    {
        $response = $this->get($url.(str_contains($url, '?') ? '&' : '?').'format=csv')->assertOk();
        $body = $response->streamedContent();
        self::assertStringStartsWith("\xEF\xBB\xBF", $body);
        $lines = array_map('str_getcsv', array_filter(explode("\r\n", substr($body, 3)), fn ($l) => $l !== ''));

        return [$lines[0], array_slice(array_values($lines), 1)];
    }

    private function exported(): int
    {
        return DB::table('account_audit_events')->where('event_type', 'reports.exported')->count();
    }

    public function test_notification_export_is_the_accounts_own_box_in_the_boxs_order(): void
    {
        $today = now()->toDateString();
        $other = StaffFixture::account('colleague', [1 => ['data-entry']]);
        StaffFixture::notice(1, $this->account->id, 'message', 'today', $today, ['title' => 'عنوان', 'sender_account_id' => $other->id]);
        StaffFixture::notice(1, $this->account->id, 'state', 'in two days', now()->addDays(2)->toDateString());
        StaffFixture::notice(1, $this->account->id, 'message', 'too far ahead', now()->addDays(9)->toDateString());
        StaffFixture::notice(1, $this->account->id, 'message', 'already seen', $today, ['seen' => 1]);
        StaffFixture::notice(1, $other->id, 'message', 'someone else', $today);
        StaffFixture::notice(1, null, 'visit', 'shared copy', $today);

        [$head, $rows] = $this->csv('/en/notifications/export');
        self::assertSame(['City', 'Date', 'Type', 'Project number', 'Title', 'Message', 'Sender'], $head);
        self::assertSame(['in two days', 'today'], array_column($rows, 5), 'unseen, up to three days ahead, newest date first');
        self::assertSame(['عنوان', 'colleague'], [$rows[1][4], $rows[1][6]]);
        self::assertSame(1, $this->exported());
        self::assertCount(2, $this->csv('/en/notifications/export?cities[]=1')[1]);
        self::assertCount(2, $this->csv('/en/notifications/export?cities[]=2&cities[]=1')[1], 'a city with no notices adds none');

        DB::table('account_city_capabilities')->where('capability', 'notifications.view')->update(['revoked_at' => now()]);
        $this->get('/en/notifications/export')->assertForbidden();
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'access.denied')->count());
        $this->get('/en/notifications/export?format=bad')->assertSessionHasErrors('format');
    }

    public function test_audit_log_is_scoped_to_the_administrators_cities_and_filterable(): void
    {
        $staff = StaffFixture::account('city-one-clerk', [1 => ['data-entry']]);
        $outsider = StaffFixture::account('city-two-clerk', [2 => ['data-entry']]);
        $event = fn (?int $actor, string $type, string $outcome, ?int $city, string $at, ?int $subject = null) => DB::table('account_audit_events')->insert([
            'actor_account_id' => $actor, 'subject_account_id' => $subject, 'city_id' => $city, 'event_type' => $type, 'outcome' => $outcome, 'occurred_at' => $at,
        ]);
        $event($staff->id, 'projects.list', 'succeeded', 1, '2026-10-20 10:00:00.000000');
        $event($staff->id, 'login.succeeded', 'succeeded', null, '2026-10-21 08:00:00.000000');
        $event($outsider->id, 'login.succeeded', 'succeeded', null, '2026-10-21 09:00:00.000000');
        $event($outsider->id, 'projects.list', 'succeeded', 2, '2026-10-22 09:00:00.000000');
        $event($staff->id, 'access.denied', 'denied', 1, '2026-10-23 09:00:00.000000');
        DB::table('account_city_capabilities')->where('city_id', 2)->where('account_id', $this->account->id)->delete();

        [$head, $rows] = $this->csv('/en/admin/audit/export');
        self::assertSame(['Event number', 'Time (UTC)', 'Event', 'Outcome', 'Actor', 'City', 'Subject type', 'Subject number', 'Subject account'], $head);
        $mine = array_values(array_filter($rows, fn ($r) => $r[4] !== $this->account->username));
        self::assertSame(['access.denied', 'login.succeeded', 'projects.list'], array_column($mine, 2), 'city one events and the sign-in of its clerk, newest first; nothing of city two');
        self::assertNotContains('city-two-clerk', array_column($rows, 4));

        self::assertSame(['access.denied'], array_column($this->csv('/en/admin/audit/export?outcome=denied')[1], 2));
        self::assertSame(['login.succeeded'], array_values(array_unique(array_column($this->csv('/en/admin/audit/export?event=login.succeeded')[1], 2))));
        self::assertSame(['projects.list'], array_column(array_filter($this->csv('/en/admin/audit/export?from=2026-10-20&to=2026-10-20')[1], fn ($r) => $r[4] === 'city-one-clerk'), 2));
        self::assertCount(0, array_filter($this->csv('/en/admin/audit/export?actor=city-two')[1], fn ($r) => $r[4] === 'city-two-clerk'));
        self::assertCount(3, $this->csv('/en/admin/audit/export?actor=clerk')[1], 'the three events of the city-one clerk');
        $this->get('/en/admin/audit/export?outcome=weird')->assertSessionHasErrors('outcome');
        $this->get('/en/admin/audit/export?event=drop table')->assertSessionHasErrors('event');
    }

    public function test_audit_log_pages_export_everything_and_need_the_manage_grant(): void
    {
        foreach (range(1, 120) as $n) {
            DB::table('account_audit_events')->insert(['actor_account_id' => $this->account->id, 'city_id' => 1, 'event_type' => 'projects.list', 'outcome' => 'succeeded', 'occurred_at' => '2026-10-20 10:00:'.str_pad((string) ($n % 60), 2, '0', STR_PAD_LEFT).'.000000']);
        }
        $page = $this->get('/en/admin/audit')->assertOk()->assertSee('Audit log')->assertSee('Event number');
        self::assertSame(50, substr_count($page->getContent(), '<tr><td>'), 'a page shows 50 events (the administrator\'s own view of the log is among them)');
        $rows = $this->csv('/en/admin/audit/export')[1];
        self::assertGreaterThanOrEqual(120, count($rows));
        self::assertSame(count($rows), count(array_unique(array_column($rows, 0))), 'each event once');
        self::assertSame(array_column($rows, 0), (function () use ($rows) {
            $ids = array_map('intval', array_column($rows, 0));
            rsort($ids);

            return array_map('strval', $ids);
        })(), 'newest first');
        $this->get('/en/admin/audit?outcome=succeeded')->assertOk()->assertSee('/en/admin/audit/export?outcome=succeeded&amp;format=xlsx', false)->assertSee('Export all matching events (Excel)');
        $this->get('/ar/admin/audit')->assertOk()->assertSee('سجل التدقيق');

        DB::table('account_city_capabilities')->where('capability', 'accounts.manage')->update(['revoked_at' => now()]);
        $this->get('/en/admin/audit')->assertForbidden();
        $this->get('/en/admin/audit/export')->assertForbidden();
    }

    public function test_lookup_index_and_exports_show_linked_values_by_name_and_only_permitted_cities(): void
    {
        $donor = ApplicationFixture::row('donor', 1, ['c_ID' => 5, 'c_Name' => 'مانح', 'c_Name_abb' => 'M']);
        ApplicationFixture::row('donorgroup', 1, ['c_ID' => 9, 'c_Name' => 'مجموعة أ'], ['Donor_ID' => $donor]);
        ApplicationFixture::row('street', 2, ['c_ID' => 1, 'c_Name' => 'شارع ثان']);
        ApplicationFixture::row('street', 1, ['c_ID' => 1, 'c_Name' => 'شارع أول']);

        $this->get('/en/lookups')->assertOk()->assertSee('Lookup tables')->assertSee('Donor groups')->assertSee('/en/lookups/donorgroup/export?format=csv', false);
        $this->get('/ar/lookups')->assertOk()->assertSee('جداول البحث');

        [$head, $rows] = $this->csv('/en/lookups/donorgroup/export');
        self::assertSame(['City', 'Donor', 'ID', 'Name'], $head);
        self::assertSame([['Aleppo', 'مانح', '9', 'مجموعة أ']], $rows);
        self::assertSame(['شارع أول', 'شارع ثان'], array_column($this->csv('/en/lookups/street/export')[1], 2));
        self::assertSame(['شارع ثان'], array_column($this->csv('/en/lookups/street/export?cities[]=2')[1], 2));
        self::assertSame([], $this->csv('/en/lookups/goal/export')[1], 'an empty list exports its headings only');
        $this->get('/en/lookups/not_a_lookup/export')->assertNotFound();
        $this->get('/en/lookups/microproject/export')->assertNotFound();
        $before = $this->exported();
        $this->csv('/en/lookups/donor/export');
        self::assertSame($before + 1, $this->exported());

        DB::table('account_city_capabilities')->where('city_id', 2)->update(['revoked_at' => now()]);
        self::assertSame(['شارع أول'], array_column($this->csv('/en/lookups/street/export?cities[]=2')[1], 2), 'a city the account may not list is ignored');
        DB::table('account_city_capabilities')->where('capability', 'applications.manage')->update(['revoked_at' => now()]);
        $this->get('/en/lookups')->assertForbidden();
        $this->get('/en/lookups/street/export')->assertForbidden();
    }

    public function test_every_listed_lookup_has_a_table_and_exports(): void
    {
        foreach (LookupService::tables() as $table) {
            $this->get('/en/lookups/'.$table.'/export?format=csv')->assertOk();
        }
        $response = $this->get('/en/lookups/street/export?format=xlsx')->assertOk();
        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->baseResponse->headers->get('content-type'));
        self::assertStringStartsWith('PK', $response->streamedContent());
    }

    public function test_state_history_export_follows_the_page_authorization_and_hides_notes_from_readers(): void
    {
        $url = '/en/applications/'.$this->project.'/states/export';
        [$head, $rows] = $this->csv($url);
        self::assertSame(['City', 'Project number', 'State', 'State date', 'Current', 'Reason', 'Note'], $head);
        self::assertSame(['Accepted', 'On Hold'], array_column($rows, 2));
        self::assertSame(['Yes', 'No'], array_column($rows, 4));
        self::assertSame('Aleppo', $rows[0][0]);

        DB::table('op_microproject_state')->update(['c_Note' => 'سري']);
        self::assertSame('سري', $this->csv($url)[1][0][6], 'a manager gets the note');
        DB::table('account_city_capabilities')->where('capability', 'lifecycle.manage')->update(['revoked_at' => now()]);
        self::assertSame('', $this->csv($url)[1][0][6], 'a reader without lifecycle.manage does not get the note');

        DB::table('account_city_capabilities')->where('capability', 'applications.view')->update(['revoked_at' => now()]);
        $this->get($url)->assertForbidden();
        $this->get('/en/applications/999999/states/export')->assertNotFound();
    }

    public function test_family_export_lists_the_providers_members_with_relation_marital_status_and_work(): void
    {
        $person = $this->intake['person'];
        DB::table('op_person')->where('id', $person)->update(['c_P_ID' => 4242, 'c_P_FirstName' => 'سامر', 'c_P_LastName' => 'خوري', 'c_P_FatherName' => 'جورج']);
        $self = ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        $son = ApplicationFixture::row('relation', 1, ['c_ID' => 3, 'c_Name' => 'ابن', 'c_Name_en' => 'Son']);
        $single = ApplicationFixture::row('maritalstatus', 1, ['c_ID' => 1, 'c_Name' => 'أعزب', 'c_Name_en' => 'Single', 'c_Mark' => 0]);
        $family = ApplicationFixture::row('family', 1, ['c_F_ID' => 7, 'c_F_Number' => 'F-7']);
        $child = ApplicationFixture::row('person', 1, ['c_P_ID' => 4243, 'c_P_FirstName' => 'ليلى', 'c_P_LastName' => 'خوري', 'c_P_FatherName' => 'سامر', 'c_P_MotherName' => 'ريم', 'c_P_DOB' => '2012-05-06']);
        DB::table('op_person')->where('id', $child)->update([app(OperationalTables::class)->relation('person', 'P_MaritalStatus_ID')['resolved_column'] => $single]);
        ApplicationFixture::row('person_family', 1, ['c_Family_ID' => 7, 'c_Work_Name' => 'مدرسة'], ['Person_ID' => $child, 'Family_ID' => $family, 'P_Provider_ID' => $person, 'Relation_ID' => $son]);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $person, 'Family_ID' => $family, 'P_Provider_ID' => $person, 'Relation_ID' => $self]);
        $stranger = ApplicationFixture::row('person', 1, ['c_P_ID' => 5000, 'c_P_FirstName' => 'غريب', 'c_P_LastName' => 'آخر']);
        ApplicationFixture::row('person_family', 1, [], ['Person_ID' => $stranger, 'P_Provider_ID' => $stranger, 'Relation_ID' => $self]);

        [$head, $rows] = $this->csv('/en/people/'.$person.'/family/export');
        self::assertSame(['City', 'Person number', 'Family number', 'Family book number', 'Name', 'Mother', 'Marital status', 'Relation', 'Work', 'Date of birth'], $head);
        self::assertCount(2, $rows, "only the provider's own family");
        $byName = array_column($rows, null, 4);
        self::assertSame(['4243', '7', 'F-7', 'ريم', 'أعزب', 'ابن', 'مدرسة', '2012-05-06'], [$byName['ليلى خوري ابن/ة سامر'][1], $byName['ليلى خوري ابن/ة سامر'][2], $byName['ليلى خوري ابن/ة سامر'][3], $byName['ليلى خوري ابن/ة سامر'][5], $byName['ليلى خوري ابن/ة سامر'][6], $byName['ليلى خوري ابن/ة سامر'][7], $byName['ليلى خوري ابن/ة سامر'][8], $byName['ليلى خوري ابن/ة سامر'][9]]);
        self::assertSame('مستفيد', $byName['سامر خوري ابن/ة جورج'][7]);

        $before = $this->exported();
        $this->csv('/en/people/'.$person.'/family/export');
        self::assertSame($before + 1, $this->exported());
        DB::table('account_city_capabilities')->where('capability', 'people.view')->update(['revoked_at' => now()]);
        $this->get('/en/people/'.$person.'/family/export')->assertForbidden();
        $this->get('/en/people/999999/family/export')->assertNotFound();
    }

    public function test_the_existing_pages_link_to_their_exports(): void
    {
        $person = $this->intake['person'];
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('/en/applications/'.$this->project.'/states/export?format=xlsx', false)->assertSee('Export the state history (Excel)');
        $this->get('/ar/people/'.$person)->assertOk()->assertSee('/ar/people/'.$person.'/family/export?format=xlsx', false);
        $this->get('/en/notifications')->assertOk()->assertSee('/en/notifications/export?format=xlsx', false)->assertSee('Export these notices (Excel)');
        $this->get('/en/admin/accounts')->assertOk()->assertSee('/en/admin/audit', false)->assertSee('/en/lookups', false);
    }
}
