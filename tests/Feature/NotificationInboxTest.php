<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\VisitScheduleService;
use App\Services\Staff\NotificationInboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\Support\StaffFixture;
use Tests\TestCase;

final class NotificationInboxTest extends TestCase
{
    private WebAccount $account;

    private NotificationInboxService $inbox;

    private int $project;

    /** @var array<string, int> source_records ids of the invented city-1 staff */
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        StaffFixture::schema();
        $this->account = ApplicationFixture::account();
        StaffFixture::grant($this->account);
        FieldFixture::grant($this->account, [1, 2], ['visits.view', 'visits.manage']);
        $this->inbox = app(NotificationInboxService::class);
        $this->project = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(1));
        $this->users = [
            'me' => StaffFixture::user(1, 7, 'staff.me'), 'other' => StaffFixture::user(1, 8, 'staff.other'),
            'fin1' => StaffFixture::user(1, 9, 'finance.one', ['UserRoleID' => 3]), 'fin2' => StaffFixture::user(1, 10, 'finance.two', ['UserRoleID' => 3]),
            'hidden' => StaffFixture::user(1, 11, 'left.the.office', ['IsVisitor' => -1]),
        ];
    }

    private function link(string $who): int
    {
        return StaffFixture::link($this->account, 1, StaffFixture::principal(1, $this->users[$who]));
    }

    private function notice(string $to, string $body, string $date, array $fields = [], ?int $project = null, ?string $sender = null): int
    {
        $userId = (int) DB::table('legacy_a_user')->where('record_id', $this->users[$to])->value('UserID');

        return StaffFixture::notice(1, $this->users[$to], $userId, $body, $date, $fields, $project, $sender === null ? null : $this->users[$sender]);
    }

    private function bodies(array $filters = []): array
    {
        return $this->inbox->inbox($this->account, $filters)['rows']->pluck('c_Body')->all();
    }

    private function assertStatus(int $status, callable $call): void
    {
        try {
            $call();
            self::fail('Expected HTTP '.$status);
        } catch (HttpException $e) {
            self::assertSame($status, $e->getStatusCode());
        }
    }

    private function seen(int $id): int
    {
        return (int) DB::table('op_user_notification')->where('id', $id)->value('c_Seen');
    }

    public function test_the_box_holds_only_the_linked_users_unseen_notices_up_to_three_days_ahead(): void
    {
        $this->link('me');
        $today = now()->toDateString();
        $this->notice('me', 'due now', $today, [], $this->project, 'other');
        $this->notice('me', 'in three days', now()->addDays(3)->toDateString());
        $this->notice('me', 'in four days', now()->addDays(4)->toDateString());
        $this->notice('me', 'already seen', $today, ['c_Seen' => 1]);
        $this->notice('other', 'for someone else', $today);

        self::assertSame(['in three days', 'due now'], $this->bodies());
        self::assertSame(2, $this->inbox->count($this->account));
        $first = $this->inbox->inbox($this->account, [])['rows']->last();
        self::assertSame('staff.other', $first->sender);
        self::assertSame($this->project, $first->project_id);
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'notifications.list')->count());
    }

    public function test_an_account_without_a_linked_identity_or_permission_has_an_empty_box_or_is_refused(): void
    {
        $this->notice('me', 'unlinked', now()->toDateString());
        self::assertSame([], $this->bodies());
        self::assertFalse($this->inbox->inbox($this->account, [])['linked']);
        self::assertSame(0, $this->inbox->count($this->account));

        $link = $this->link('me');
        self::assertSame(['unlinked'], $this->bodies());
        DB::table('account_legacy_links')->where('id', $link)->update(['revoked_at' => now()]);
        self::assertSame([], $this->bodies(), 'a revoked link gives no notices');
        DB::table('account_legacy_links')->where('id', $link)->update(['revoked_at' => null]);
        DB::table('account_city_capabilities')->where('capability', 'notifications.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->inbox->inbox($this->account, []));
        self::assertSame(0, $this->inbox->count($this->account));
    }

    public function test_marking_seen_clears_every_copy_but_only_for_notices_in_the_box(): void
    {
        $this->link('me');
        $today = now()->toDateString();
        $mine = $this->notice('me', 'Visit Opening-O', $today, ['c_Pay_ID' => -21], $this->project);
        $copy = $this->notice('other', 'Visit Opening-O', $today, ['c_Pay_ID' => -21], $this->project);
        $different = $this->notice('other', 'Visit Opening-O', $today, ['c_Pay_ID' => -21, 'c_P_Name' => 'someone else'], $this->project);
        $theirs = $this->notice('other', 'private', $today);
        $this->notice('me', 'stays', $today);

        self::assertSame(1, $this->inbox->markSeen($this->account, [$mine, $theirs, 999999, $mine]));
        self::assertSame([1, 1, 0, 0], [$this->seen($mine), $this->seen($copy), $this->seen($different), $this->seen($theirs)]);
        self::assertSame(['stays'], $this->bodies());
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'notifications.seen')->count());
    }

    public function test_financial_staff_share_one_box_up_to_today_and_clear_a_payment_everywhere(): void
    {
        $this->link('fin1');
        $today = now()->toDateString();
        $own = $this->notice('fin1', '1500', $today, ['c_Pay_ID' => 55]);
        $other = $this->notice('fin2', '1500', $today, ['c_Pay_ID' => 55]);
        $later = $this->notice('fin2', '2500', $today, ['c_Pay_ID' => 56]);
        $this->notice('fin2', 'future', now()->addDay()->toDateString(), ['c_Pay_ID' => 57]);
        $this->notice('other', 'not financial', $today, ['c_Pay_ID' => 58]);

        $rows = $this->inbox->inbox($this->account, [])['rows'];
        self::assertEqualsCanonicalizing([$own, $other, $later], $rows->pluck('id')->all());
        self::assertEqualsCanonicalizing(['1,500', '1,500', '2,500'], $rows->pluck('amount')->all());
        self::assertSame(3, $this->inbox->count($this->account));

        $this->inbox->markSeen($this->account, [$own]);
        self::assertSame([1, 1, 0], [$this->seen($own), $this->seen($other), $this->seen($later)]);
    }

    public function test_messages_go_to_one_user_or_everyone_and_are_validated(): void
    {
        $this->link('me');
        $inbox = $this->inbox;
        $send = fn (array $input) => $inbox->send($this->account, $input + ['city' => 1, 'to' => 'all', 'body' => 'Please call the family', 'title' => 'Follow-up', 'date' => '2026-10-23']);
        self::assertSame(4, $send([]), 'everyone except users marked IsVisitor = -1');
        $messages = DB::table('op_user_notification')->get();
        self::assertSame([-100], $messages->pluck('c_Pay_ID')->map(fn ($v) => (int) $v)->unique()->values()->all());
        self::assertEqualsCanonicalizing([7, 8, 9, 10], $messages->pluck('c_User_ID')->map(fn ($v) => (int) $v)->all());
        self::assertSame([7], $messages->pluck('c_Sender_ID')->map(fn ($v) => (int) $v)->unique()->values()->all(), 'the linked source user is the sender');
        self::assertSame('Please call the family', $messages->first()->c_Body);
        self::assertSame('2026-10-23', substr((string) $messages->first()->c_Date, 0, 10));

        self::assertSame(1, $send(['to' => (string) $this->users['other']]));
        self::assertSame(8, (int) DB::table('op_user_notification')->latest('id')->value('c_User_ID'));
        $before = DB::table('op_user_notification')->count();
        foreach ([['body' => '  '], ['title' => str_repeat('ا', 200)], ['date' => '2026-02-30'], ['to' => '999999'], ['to' => 'abc'], ['to' => (string) $this->users['hidden']], ['project' => '1']] as $bad) {
            try {
                $send($bad);
                self::fail('Expected a validation error');
            } catch (ValidationException $e) {
                self::assertNotEmpty($e->errors());
            }
        }
        self::assertSame($before, DB::table('op_user_notification')->count());
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'notifications.sent')->count());

        $this->assertStatus(403, fn () => $inbox->send($this->account, ['city' => 3, 'to' => 'all', 'body' => 'x']));
        DB::table('account_city_capabilities')->where('capability', 'notifications.send')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $send([]));
    }

    public function test_only_administrators_attach_a_project_and_an_unlinked_sender_stays_anonymous(): void
    {
        $number = (int) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID');
        $this->inbox->send($this->account, ['city' => 1, 'to' => (string) $this->users['other'], 'body' => 'plain', 'date' => '', 'title' => '']);
        $plain = DB::table('op_user_notification')->first();
        self::assertNull($plain->c_Sender_ID);
        self::assertSame('null', $plain->ref_113ed6c1bf02_state);
        self::assertSame(now()->toDateString(), substr((string) $plain->c_Date, 0, 10), 'the date defaults to today');
        try {
            $this->inbox->send($this->account, ['city' => 1, 'to' => 'all', 'body' => 'x', 'project' => (string) $number]);
            self::fail('Expected a refusal');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('project', $e->errors());
        }
        StaffFixture::grant($this->account, [1], ['accounts.manage']);
        $this->inbox->send($this->account, ['city' => 1, 'to' => (string) $this->users['other'], 'body' => 'about the project', 'project' => (string) $number]);
        $attached = DB::table('op_user_notification')->latest('id')->first();
        self::assertSame($number, (int) $attached->c_MicroProject_ID);
        self::assertSame($this->project, (int) $attached->ref_5f0c797b1aae_id);
        try {
            $this->inbox->send($this->account, ['city' => 1, 'to' => 'all', 'body' => 'x', 'project' => '999999']);
            self::fail('Expected an unknown project');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('project', $e->errors());
        }
    }

    public function test_the_visit_schedule_notes_and_hides_notices_for_staff_who_manage_visits(): void
    {
        $notice = fn (string $body, array $fields = []) => ApplicationFixture::row('user_notification', 1, $fields + ['c_Pay_ID' => -21, 'c_Body' => $body, 'c_Date' => '2026-11-05', 'c_P_Name' => 'Synthetic beneficiary', 'c_Seen' => 0, 'c_MicroProject_ID' => 1000], ['MicroProject_ID' => $this->project]);
        $visit = $notice('Visit Opening-O');
        $twin = $notice('Visit Opening-O');
        $other = $notice('Visit M1-O');
        $message = $notice('private', ['c_Pay_ID' => -100]);
        $schedule = app(VisitScheduleService::class);

        $schedule->setNote($this->account, $visit, ' exact  note ');
        self::assertSame(' exact  note ', DB::table('op_user_notification')->where('id', $visit)->value('c_Note'));
        self::assertNull(DB::table('op_user_notification')->where('id', $twin)->value('c_Note'));
        $schedule->setSeen($this->account, $visit, 1);
        self::assertSame([1, 1, 0], [$this->seen($visit), $this->seen($twin), $this->seen($other)]);
        $schedule->setSeen($this->account, $twin, 0);
        self::assertSame([0, 0], [$this->seen($visit), $this->seen($twin)]);
        self::assertSame([1, 1], [DB::table('account_audit_events')->where('event_type', 'notifications.noted')->count(), DB::table('account_audit_events')->where('event_type', 'notifications.seen')->count() - 1]);

        $this->assertStatus(404, fn () => $schedule->setNote($this->account, $message, 'x'));
        $this->assertStatus(422, fn () => $schedule->setNote($this->account, $visit, str_repeat('x', 65536)));
        DB::table('account_city_capabilities')->where('capability', 'visits.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $schedule->setSeen($this->account, $visit, 1));
        $this->assertStatus(403, fn () => $schedule->setNote($this->account, $visit, 'x'));
        self::assertSame([], $schedule->schedule($this->account, [])['manageCities']);
    }

    public function test_pages_and_posts_work_over_http_in_both_languages(): void
    {
        $this->link('me');
        $today = now()->toDateString();
        $id = $this->notice('me', 'Call the beneficiary', $today, [], $this->project);
        $this->actingAs($this->account);
        $this->get('/en/notifications')->assertOk()->assertSee('Notifications')->assertSee('Call the beneficiary')->assertSee('(1)')->assertSee('Send a message');
        $this->get('/ar/notifications')->assertOk()->assertSee('dir="rtl"', false)->assertSee('الإشعارات');
        $this->get('/en/notifications?cities[]=x')->assertSessionHasErrors('cities.0');
        $this->post('/en/notifications/seen', [])->assertSessionHasErrors('ids');
        $this->post('/en/notifications/seen', ['ids' => [$id]])->assertRedirect('/en/notifications');
        self::assertSame(1, $this->seen($id));
        $this->get('/en/notifications')->assertOk()->assertSee('No unseen notices.');

        $this->get('/en/notifications/send')->assertOk()->assertSee('Everyone')->assertSee('staff.other')->assertDontSee('left.the.office');
        $this->post('/en/notifications/send', ['city' => 1, 'to' => 'all', 'body' => 'Hello team', 'title' => '', 'date' => ''])->assertRedirect();
        self::assertSame(4, DB::table('op_user_notification')->where('c_Body', 'Hello team')->count());
        $this->post('/en/notifications/send', ['city' => 1, 'to' => 'all', 'body' => ''])->assertSessionHasErrors('body');
        $this->get('/en/notifications/send?city=2')->assertOk();
        $this->get('/en/notifications/send?city=3')->assertForbidden();

        $notice = ApplicationFixture::row('user_notification', 1, ['c_Pay_ID' => -21, 'c_Body' => 'Visit M2-O', 'c_Date' => '2026-11-05', 'c_P_Name' => 'p', 'c_Seen' => 0, 'c_MicroProject_ID' => 1000], ['MicroProject_ID' => $this->project]);
        $this->get('/en/visit-schedule')->assertOk()->assertSee('Hide')->assertSee('name="note"', false);
        $this->post("/en/visit-schedule/{$notice}/note", ['note' => 'Call first'])->assertRedirect();
        self::assertSame('Call first', DB::table('op_user_notification')->where('id', $notice)->value('c_Note'));
        $this->post("/en/visit-schedule/{$notice}/seen", ['seen' => 1])->assertRedirect();
        self::assertSame(1, $this->seen($notice));
        $this->post("/en/visit-schedule/{$notice}/seen", ['seen' => 7])->assertSessionHasErrors('seen');
        $this->get('/en/visit-schedule?seen=1')->assertOk()->assertSee('Restore');

        DB::table('account_city_capabilities')->where('capability', 'notifications.view')->update(['revoked_at' => now()]);
        $this->get('/en/notifications')->assertForbidden();
        $this->get('/en/applications')->assertOk()->assertDontSee('Notifications');
    }
}
