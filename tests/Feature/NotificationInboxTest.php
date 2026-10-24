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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        StaffFixture::grant($this->account);
        FieldFixture::grant($this->account, [1, 2], ['visits.view', 'visits.manage']);
        $this->inbox = app(NotificationInboxService::class);
        $this->project = FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(1));
        DB::table('web_notices')->delete();
    }

    private function colleague(string $name, array $roles = [1 => ['data-entry']]): WebAccount
    {
        $account = StaffFixture::account($name, $roles);
        StaffFixture::grant($account, [1], ['notifications.view', 'notifications.send']);

        return $account;
    }

    private function bodies(array $filters = [], ?WebAccount $who = null): array
    {
        return $this->inbox->inbox($who ?? $this->account, $filters)['rows']->pluck('body')->all();
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

    private function invalid(string $field, callable $call): void
    {
        try {
            $call();
            self::fail('Expected a validation error for '.$field);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());
        }
    }

    private function seen(int $id): int
    {
        return (int) DB::table('web_notices')->where('id', $id)->value('seen');
    }

    public function test_the_box_holds_only_the_accounts_unseen_notices_up_to_three_days_ahead(): void
    {
        $other = $this->colleague('someone.else');
        $today = now()->toDateString();
        StaffFixture::notice(1, $this->account->id, 'state', 'due now', $today, ['sender_account_id' => $other->id], $this->project);
        StaffFixture::notice(1, $this->account->id, 'message', 'in three days', now()->addDays(3)->toDateString());
        StaffFixture::notice(1, $this->account->id, 'message', 'in four days', now()->addDays(4)->toDateString());
        StaffFixture::notice(1, $this->account->id, 'message', 'already seen', $today, ['seen' => 1]);
        StaffFixture::notice(1, $other->id, 'message', 'for someone else', $today);
        StaffFixture::notice(1, null, 'visit', 'shared copy', $today);
        StaffFixture::notice(2, $this->account->id, 'message', 'other city', $today);

        self::assertSame(['in three days', 'other city', 'due now'], $this->bodies());
        self::assertSame(['in three days', 'due now'], $this->bodies(['cities' => [1]]));
        self::assertSame(3, $this->inbox->count($this->account));
        $first = $this->inbox->inbox($this->account, ['cities' => [1]])['rows']->last();
        self::assertSame('someone.else', $first->sender);
        self::assertSame($this->project, (int) $first->project_id);
        self::assertSame(3, DB::table('account_audit_events')->where('event_type', 'notifications.list')->count());
    }

    public function test_an_account_without_permission_is_refused_and_one_without_notices_sees_an_empty_box(): void
    {
        StaffFixture::notice(1, $this->account->id, 'message', 'mine', now()->toDateString());
        self::assertSame(['mine'], $this->bodies());
        $quiet = $this->colleague('quiet');
        self::assertSame([], $this->bodies([], $quiet));
        self::assertSame(0, $this->inbox->count($quiet));
        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->where('capability', 'notifications.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->inbox->inbox($this->account, []));
        self::assertSame(0, $this->inbox->count($this->account));
    }

    public function test_marking_seen_clears_every_copy_but_only_for_notices_in_the_box(): void
    {
        $other = $this->colleague('someone.else');
        $today = now()->toDateString();
        $mine = StaffFixture::notice(1, $this->account->id, 'state', 'moved to Closed', $today, [], $this->project);
        $copy = StaffFixture::notice(1, $other->id, 'state', 'moved to Closed', $today, [], $this->project);
        $different = StaffFixture::notice(1, $other->id, 'state', 'moved to Closed', $today, ['title' => 'someone else'], $this->project);
        $theirs = StaffFixture::notice(1, $other->id, 'message', 'private', $today);
        StaffFixture::notice(1, $this->account->id, 'message', 'stays', $today);

        self::assertSame(1, $this->inbox->markSeen($this->account, [$mine, $theirs, 999999, $mine]));
        self::assertSame([1, 1, 0, 0], [$this->seen($mine), $this->seen($copy), $this->seen($different), $this->seen($theirs)]);
        self::assertSame(['stays'], $this->bodies());
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'notifications.seen')->count());
    }

    public function test_financial_accounts_read_up_to_today_and_a_payment_clears_for_all_its_recipients(): void
    {
        $finance = $this->colleague('finance.one', [1 => ['financial']]);
        $second = $this->colleague('finance.two', [1 => ['financial']]);
        $today = now()->toDateString();
        $own = StaffFixture::notice(1, $finance->id, 'payment', '1500', $today, ['pay_key' => 55]);
        $other = StaffFixture::notice(1, $second->id, 'payment', '1500', $today, ['pay_key' => 55]);
        $later = StaffFixture::notice(1, $finance->id, 'payment', '2500', $today, ['pay_key' => 56]);
        StaffFixture::notice(1, $finance->id, 'payment', 'future', now()->addDay()->toDateString(), ['pay_key' => 57]);

        $rows = $this->inbox->inbox($finance, [])['rows'];
        self::assertEqualsCanonicalizing([$own, $later], $rows->pluck('id')->all(), 'a financial account reads up to today only');
        self::assertEqualsCanonicalizing(['1,500', '2,500'], $rows->pluck('amount')->all());
        self::assertSame(2, $this->inbox->count($finance));

        $this->inbox->markSeen($finance, [$own]);
        self::assertSame([1, 1, 0], [$this->seen($own), $this->seen($other), $this->seen($later)]);
    }

    public function test_messages_go_to_one_account_or_everyone_who_reads_notices_and_are_validated(): void
    {
        $one = $this->colleague('one');
        $two = $this->colleague('two');
        $plain = StaffFixture::account('no.notices');
        StaffFixture::grant($plain, [1], ['people.view']);
        $inbox = $this->inbox;
        $send = fn (array $input) => $inbox->send($this->account, $input + ['city' => 1, 'to' => 'all', 'body' => 'Please call the family', 'title' => 'Follow-up', 'date' => '2026-10-23']);
        self::assertSame(3, $send([]), 'everyone who reads notifications in the city, the sender included');
        $messages = DB::table('web_notices')->get();
        self::assertSame(['message'], $messages->pluck('kind')->unique()->values()->all());
        self::assertEqualsCanonicalizing([$this->account->id, $one->id, $two->id], $messages->pluck('account_id')->map(fn ($v) => (int) $v)->all());
        self::assertSame([$this->account->id], $messages->pluck('sender_account_id')->map(fn ($v) => (int) $v)->unique()->values()->all());
        self::assertSame('Please call the family', $messages->first()->body);
        self::assertSame('Follow-up', $messages->first()->title);
        self::assertSame('2026-10-23', substr((string) $messages->first()->notice_date, 0, 10));

        self::assertSame(1, $send(['to' => (string) $two->id]));
        self::assertSame($two->id, (int) DB::table('web_notices')->latest('id')->value('account_id'));
        $before = DB::table('web_notices')->count();
        foreach ([['body' => '  '], ['title' => str_repeat('ا', 200)], ['date' => '2026-02-30'], ['to' => '999999'], ['to' => 'abc'], ['to' => (string) $plain->id], ['project' => '1']] as $bad) {
            try {
                $send($bad);
                self::fail('Expected a validation error');
            } catch (ValidationException $e) {
                self::assertNotEmpty($e->errors());
            }
        }
        self::assertSame($before, DB::table('web_notices')->count());
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'notifications.sent')->count());

        $this->assertStatus(403, fn () => $inbox->send($this->account, ['city' => 3, 'to' => 'all', 'body' => 'x']));
        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->where('capability', 'notifications.send')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $send([]));
    }

    public function test_only_administrators_attach_a_project_to_a_message(): void
    {
        $number = (int) DB::table('op_microproject')->where('id', $this->project)->value('c_MP_ID');
        $one = $this->colleague('one');
        $this->inbox->send($this->account, ['city' => 1, 'to' => (string) $one->id, 'body' => 'plain', 'date' => '', 'title' => '']);
        $plain = DB::table('web_notices')->first();
        self::assertSame(now()->toDateString(), substr((string) $plain->notice_date, 0, 10), 'the date defaults to today');
        self::assertNull($plain->project_id);
        $this->invalid('project', fn () => $this->inbox->send($this->account, ['city' => 1, 'to' => 'all', 'body' => 'x', 'project' => (string) $number]));
        StaffFixture::grant($this->account, [1], ['accounts.manage']);
        $this->inbox->send($this->account, ['city' => 1, 'to' => (string) $one->id, 'body' => 'about the project', 'project' => (string) $number]);
        $attached = DB::table('web_notices')->latest('id')->first();
        self::assertSame($number, (int) $attached->project_number);
        self::assertSame($this->project, (int) $attached->project_id);
        $this->invalid('project', fn () => $this->inbox->send($this->account, ['city' => 1, 'to' => 'all', 'body' => 'x', 'project' => '999999']));
    }

    public function test_the_visit_schedule_notes_and_hides_notices_for_staff_who_manage_visits(): void
    {
        $notice = fn (string $body, array $fields = []) => StaffFixture::notice(1, null, 'visit', $body, '2026-11-05', $fields, $this->project);
        $visit = $notice('Visit Opening-O');
        $twin = $notice('Visit Opening-O');
        $other = $notice('Visit M1-O');
        $mine = StaffFixture::notice(1, $this->account->id, 'message', 'private', '2026-11-05');
        $schedule = app(VisitScheduleService::class);

        $schedule->setNote($this->account, $visit, ' exact  note ');
        self::assertSame(' exact  note ', DB::table('web_notices')->where('id', $visit)->value('note'));
        self::assertNull(DB::table('web_notices')->where('id', $twin)->value('note'));
        $schedule->setSeen($this->account, $visit, 1);
        self::assertSame([1, 1, 0], [$this->seen($visit), $this->seen($twin), $this->seen($other)]);
        $schedule->setSeen($this->account, $twin, 0);
        self::assertSame([0, 0], [$this->seen($visit), $this->seen($twin)]);
        self::assertSame(1, DB::table('account_audit_events')->where('event_type', 'notifications.noted')->count());

        $this->assertStatus(404, fn () => $schedule->setNote($this->account, $mine, 'x'));
        $this->assertStatus(422, fn () => $schedule->setNote($this->account, $visit, str_repeat('x', 65536)));
        DB::table('account_city_capabilities')->where('capability', 'visits.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $schedule->setSeen($this->account, $visit, 1));
        $this->assertStatus(403, fn () => $schedule->setNote($this->account, $visit, 'x'));
        self::assertSame([], $schedule->schedule($this->account, [])['manageCities']);
    }

    public function test_an_unseen_visit_is_rescheduled_with_a_reason_and_later_visits_can_follow(): void
    {
        $visit = fn (string $body, string $date, array $fields = []) => StaffFixture::notice(1, null, 'visit', $body, $date, $fields, $this->project);
        $opening = $visit('Visit Opening-O', '2026-11-05');
        $first = $visit('Visit M1-O', '2027-05-05');
        $second = $visit('Visit M2-O', '2027-11-05');
        $done = $visit('Visit M3-O', '2028-05-05', ['seen' => 1]);
        $elsewhere = StaffFixture::notice(1, null, 'visit', 'Visit M1-O', '2027-05-05', [], FinanceFixture::acceptedProject($this->account, ApplicationFixture::intake(2), 2));
        $schedule = app(VisitScheduleService::class);
        $date = fn (int $id) => substr((string) DB::table('web_notices')->where('id', $id)->value('notice_date'), 0, 10);

        // One visit alone, with a reason; the original date is kept.
        self::assertSame(1, $schedule->reschedule($this->account, $first, '2027-06-01', 'Beneficiary travelling', false));
        self::assertSame(['2027-06-01', '2026-11-05', '2027-11-05'], [$date($first), $date($opening), $date($second)]);
        $row = DB::table('web_notices')->where('id', $first)->first();
        self::assertSame('2027-05-05', substr((string) $row->original_date, 0, 10));
        self::assertSame('Beneficiary travelling', $row->change_reason);

        // Moving again keeps the very first date, and later unseen visits of the same project follow by the same number of days.
        self::assertSame(2, $schedule->reschedule($this->account, $first, '2027-06-11', 'Moved again', true));
        self::assertSame(['2027-06-11', '2027-11-15', '2028-05-05'], [$date($first), $date($second), $date($done)], 'a seen visit never moves');
        self::assertSame('2027-05-05', substr((string) DB::table('web_notices')->where('id', $first)->value('original_date'), 0, 10));
        self::assertSame('2027-11-05', substr((string) DB::table('web_notices')->where('id', $second)->value('original_date'), 0, 10));
        self::assertSame('2027-05-05', $date($elsewhere), 'other projects are untouched');
        self::assertSame('2026-11-05', $date($opening), 'earlier visits are untouched');

        // Earlier dates work too (negative shift).
        self::assertSame(1, $schedule->reschedule($this->account, $second, '2027-11-05', 'Back', true));
        self::assertSame('2027-11-05', $date($second));

        $this->invalid('date', fn () => $schedule->reschedule($this->account, $first, '2027-06-11', 'same', false));
        $this->invalid('date', fn () => $schedule->reschedule($this->account, $first, '2027-02-30', 'bad date', false));
        $this->invalid('reason', fn () => $schedule->reschedule($this->account, $first, '2027-07-01', '   ', false));
        $this->invalid('date', fn () => $schedule->reschedule($this->account, $done, '2028-06-05', 'too late', false));
        $clearance = StaffFixture::notice(1, null, 'clearance', 'براءة الذمة (Clearance)', '2027-12-01', [], $this->project);
        $this->invalid('date', fn () => $schedule->reschedule($this->account, $clearance, '2027-12-05', 'not a visit', false));
        $this->assertStatus(404, fn () => $schedule->reschedule($this->account, 999999, '2027-12-05', 'x', false));
        DB::table('account_city_capabilities')->where('capability', 'visits.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $schedule->reschedule($this->account, $first, '2027-07-01', 'no permission', false));
        self::assertSame(3, DB::table('account_audit_events')->where('event_type', 'notifications.rescheduled')->count());
    }

    public function test_pages_and_posts_work_over_http_in_both_languages(): void
    {
        $today = now()->toDateString();
        $id = StaffFixture::notice(1, $this->account->id, 'state', 'Call the beneficiary', $today, [], $this->project);
        $everyone = $this->colleague('someone.else');
        $this->actingAs($this->account);
        $this->get('/en/notifications')->assertOk()->assertSee('Notifications')->assertSee('Call the beneficiary')->assertSee('(1)')->assertSee('Send a message');
        $this->get('/ar/notifications')->assertOk()->assertSee('dir="rtl"', false)->assertSee('الإشعارات');
        $this->get('/en/notifications?cities[]=x')->assertSessionHasErrors('cities.0');
        $this->post('/en/notifications/seen', [])->assertSessionHasErrors('ids');
        $this->post('/en/notifications/seen', ['ids' => [$id]])->assertRedirect('/en/notifications');
        self::assertSame(1, $this->seen($id));
        $this->get('/en/notifications')->assertOk()->assertSee('No unseen notices.');

        $this->get('/en/notifications/send')->assertOk()->assertSee('Everyone')->assertSee('someone.else');
        $this->post('/en/notifications/send', ['city' => 1, 'to' => 'all', 'body' => 'Hello team', 'title' => '', 'date' => ''])->assertRedirect();
        self::assertSame(2, DB::table('web_notices')->where('body', 'Hello team')->count());
        $this->post('/en/notifications/send', ['city' => 1, 'to' => 'all', 'body' => ''])->assertSessionHasErrors('body');
        $this->get('/en/notifications/send?city=2')->assertOk();
        $this->get('/en/notifications/send?city=3')->assertForbidden();

        $notice = StaffFixture::notice(1, null, 'visit', 'Visit M2-O', '2026-11-05', [], $this->project);
        $this->get('/en/visit-schedule')->assertOk()->assertSee('Hide')->assertSee('name="note"', false)->assertSee('Reschedule');
        $this->post("/en/visit-schedule/{$notice}/note", ['note' => 'Call first'])->assertRedirect();
        self::assertSame('Call first', DB::table('web_notices')->where('id', $notice)->value('note'));
        $this->post("/en/visit-schedule/{$notice}/reschedule", ['date' => '2026-12-01', 'reason' => 'Holiday', 'shift' => '1'])->assertRedirect();
        self::assertSame('2026-12-01', substr((string) DB::table('web_notices')->where('id', $notice)->value('notice_date'), 0, 10));
        $this->post("/en/visit-schedule/{$notice}/reschedule", ['date' => '2026-12-02', 'reason' => ''])->assertSessionHasErrors('reason');
        $this->get('/en/visit-schedule')->assertOk()->assertSee('Holiday')->assertSee('2026-11-05');
        $this->post("/en/visit-schedule/{$notice}/seen", ['seen' => 1])->assertRedirect();
        self::assertSame(1, $this->seen($notice));
        $this->post("/en/visit-schedule/{$notice}/seen", ['seen' => 7])->assertSessionHasErrors('seen');
        $this->post("/en/visit-schedule/{$notice}/reschedule", ['date' => '2026-12-09', 'reason' => 'after seen'])->assertSessionHasErrors('date');
        $this->get('/en/visit-schedule?seen=1')->assertOk()->assertSee('Restore');

        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->where('capability', 'notifications.view')->update(['revoked_at' => now()]);
        $this->get('/en/notifications')->assertForbidden();
        $this->get('/en/applications')->assertOk()->assertDontSee('Notifications');
        self::assertNotNull($everyone);
    }
}
