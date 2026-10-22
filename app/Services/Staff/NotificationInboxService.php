<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Operational\OperationalTables;
use App\Services\SourceIdentity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The notification box (`Notification_Box`, `SendMessage_Box`) of a web account. The account reads the unseen notices addressed to the
 * historical source users it is linked to (reviewed legacy links), in the cities where it holds `notifications.view`. Like the desktop's
 * box, a staff member whose linked user has the financial role (3) reads the notices of all financial users instead, up to today; every
 * other account reads its own notices up to three days ahead. The bell count is the size of the same list (the desktop's count and
 * list used horizons a day apart).
 */
final class NotificationInboxService
{
    public const PER_PAGE = 50;

    public const AHEAD_DAYS = 3;

    private const FINANCIAL_ROLE = 3;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private SourceIdentity $identity, private NotificationService $notices) {}

    /** Cities where the account may read its notices. */
    public function cities(WebAccount $account): Collection
    {
        return $this->access->cities($account, 'notifications.view');
    }

    /** Cities where the account may send messages. */
    public function sendCities(WebAccount $account): Collection
    {
        return $this->access->cities($account, 'notifications.send');
    }

    /** Number of unseen notices in the account's box (0 without the permission or without a linked staff identity). */
    public function count(WebAccount $account): int
    {
        $cities = $this->cities($account);

        return $cities->isEmpty() ? 0 : $this->query($account, $cities->pluck('id')->all())?->count() ?? 0;
    }

    /**
     * @param  array{cities?: list<int>}  $filters
     * @return array{rows: LengthAwarePaginator, cities: Collection, linked: bool}
     */
    public function inbox(WebAccount $account, array $filters): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $this->audit->record($account->id, 'notifications.list', 'succeeded');
        $scopes = $this->scopes($account, $ids);
        $query = $this->query($account, $ids, $scopes);
        if ($query === null) {
            return ['rows' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, self::PER_PAGE), 'cities' => $permitted, 'linked' => false];
        }
        $rows = $query->orderByDesc('p.c_Date')->orderByDesc('p.id')->paginate(self::PER_PAGE)->withQueryString();
        $projectRef = $this->tables->relation('user_notification', 'MicroProject_ID');
        $senderRef = $this->tables->relation('user_notification', 'Sender_ID');
        $senders = [];
        foreach ($rows->getCollection()->groupBy('source_id') as $source => $group) {
            $records = $group->filter(fn ($row) => $row->{$senderRef['state_column']} === 'resolved')->pluck($senderRef['resolved_column'])->unique()->all();
            $senders[(int) $source] = $records === [] ? collect() : $this->identity->publishedUsers((int) $source)->whereIn('r.id', $records)->pluck('u.UserName', 'r.id');
        }
        $capabilities = [];
        foreach ($ids as $city) {
            $capabilities[$city] = [
                'application' => $this->access->check($account, $city, 'applications.view'),
                'finance' => $this->access->check($account, $city, 'applications.view') && $this->access->check($account, $city, 'finance.view'),
                'visits' => $this->access->check($account, $city, 'applications.view') && $this->access->check($account, $city, 'visits.view'),
            ];
        }
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($projectRef, $senderRef, $senders, $capabilities, $scopes) {
            $row->project_id = $row->{$projectRef['state_column']} === 'resolved' ? (int) $row->{$projectRef['resolved_column']} : null;
            $row->sender = $row->{$senderRef['state_column']} === 'resolved' ? $senders[(int) $row->source_id]->get((int) $row->{$senderRef['resolved_column']}) : null;
            $row->links = $capabilities[(int) $row->city_id] + ['project' => $row->project_id !== null];
            $row->amount = ($scopes[(int) $row->city_id]['financial'] ?? false) && preg_match('/\A[0-9]+\z/', (string) $row->c_Body) === 1 && (int) $row->c_Body !== 0 ? number_format((int) $row->c_Body) : null;

            return $row;
        }));

        return ['rows' => $rows, 'cities' => $permitted, 'linked' => true];
    }

    /**
     * Marks the chosen notices of the account's own box seen. Only notices that are in the box are touched; the other copies of the same
     * notice are cleared with them, as on the desktop. A financial user's notice of a payment clears every notice of that payment.
     *
     * @param  list<int>  $ids
     */
    public function markSeen(WebAccount $account, array $ids): int
    {
        $cities = $this->cities($account);
        if ($cities->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }

        return DB::transaction(function () use ($account, $cities, $ids) {
            $scopes = $this->scopes($account, $cities->pluck('id')->all());
            $rows = $this->query($account, $cities->pluck('id')->all(), $scopes)?->whereIn('p.id', array_values(array_unique($ids)))->get() ?? collect();
            foreach ($rows as $row) {
                if (($scopes[(int) $row->city_id]['financial'] ?? false) && (int) $row->c_Pay_ID > 0) {
                    $this->notices->setSeenForPayment($row, 1);
                } else {
                    $this->notices->setSeenLike($row, 1);
                }
            }
            $this->audit->record($account->id, 'notifications.seen', 'succeeded');

            return $rows->count();
        });
    }

    /**
     * Recipients the account may address in a city: every active source user of the city's source (the desktop's `select_users`).
     *
     * @return Collection<int, object{record_id: int, UserID: int, UserName: string}>
     */
    public function recipients(WebAccount $account, int $city): Collection
    {
        abort_unless($this->access->check($account, $city, 'notifications.send'), 403);

        return $this->notices->recipients($this->tables->source($city), 'all')->sortBy('UserName', SORT_STRING)->values();
    }

    /**
     * Sends a message (`Pay_ID` -100) to one source user or to all of them. `project` (a project number of the city) is for administrators
     * (`accounts.manage`), as on the desktop. The sender is the source user the account is linked to in that city, if it has exactly one.
     *
     * @param  array{city: int, to: string, date?: ?string, title?: ?string, body?: ?string, project?: ?string}  $input
     */
    public function send(WebAccount $account, array $input): int
    {
        $city = (int) ($input['city'] ?? 0);
        if (! $this->access->check($account, $city, 'notifications.send')) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }

        return DB::transaction(function () use ($account, $input, $city) {
            $source = $this->tables->source($city);
            $fail = fn (string $field, string $key): never => throw ValidationException::withMessages([$field => __('staff.notifications.errors.'.$key)]);
            $body = Fields::text($input['body'] ?? '');
            (trim($body) !== '' && strlen($body) <= 16777215) || $fail('body', 'body');
            $title = Fields::text($input['title'] ?? '');
            strlen($title) <= 255 || $fail('title', 'title');
            $date = trim(Fields::text($input['date'] ?? ''));
            $date = $date === '' ? now()->toDateString() : $date;
            Fields::isDate($date) || $fail('date', 'date');
            $recipients = $this->notices->recipients($source, 'all');
            $to = (string) ($input['to'] ?? '');
            if ($to !== 'all') {
                $recipients = $recipients->where('record_id', (int) $to)->values();
                ctype_digit($to) && $recipients->isNotEmpty() || $fail('to', 'to');
            }
            $recipients->isNotEmpty() || $fail('to', 'to');
            $project = null;
            if (! Fields::blank($input['project'] ?? null)) {
                $this->access->check($account, $city, 'accounts.manage') || $fail('project', 'project_admin');
                ctype_digit(trim((string) $input['project'])) || $fail('project', 'project');
                $project = $this->tables->query('microproject')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.c_MP_ID', (int) trim((string) $input['project']))->first();
                $project !== null || $fail('project', 'project');
            }
            $sender = $this->notices->senderFor($account, $source);
            foreach ($recipients as $recipient) {
                $this->notices->add($city, $source, $project, $date, $body, $title, NotificationService::MESSAGE, $recipient, $sender);
            }
            $this->audit->record($account->id, 'notifications.sent', 'succeeded', $city);

            return $recipients->count();
        });
    }

    /**
     * Per city the account's source users: record ids and whether one of them has the financial role. Cities where it has no linked user
     * have no entry.
     *
     * @param  list<int>  $cityIds
     * @return array<int, array{source: int, users: list<int>, financial: bool, financialUsers: list<int>}>
     */
    private function scopes(WebAccount $account, array $cityIds): array
    {
        $scopes = [];
        foreach ($cityIds as $city) {
            $source = $this->tables->source($city);
            $records = $this->notices->linkedRecords($account, $source);
            if ($records === []) {
                continue;
            }
            $users = $this->identity->publishedUsers($source)->whereIn('r.id', $records)->get(['r.id', 'u.UserRoleID']);
            if ($users->isEmpty()) {
                continue;
            }
            $financial = $users->contains(fn ($user) => (int) $user->UserRoleID === self::FINANCIAL_ROLE);
            $scopes[$city] = [
                'source' => $source, 'users' => $users->pluck('id')->map(fn ($id) => (int) $id)->all(), 'financial' => $financial,
                'financialUsers' => $financial ? $this->identity->publishedUsers($source)->where('u.UserRoleID', self::FINANCIAL_ROLE)->pluck('r.id')->map(fn ($id) => (int) $id)->all() : [],
            ];
        }

        return $scopes;
    }

    /** The unseen notices of the account's box in the cities, or null when none of the cities has a linked staff identity. */
    private function query(WebAccount $account, array $cityIds, ?array $scopes = null): ?Builder
    {
        $scopes ??= $this->scopes($account, $cityIds);
        if ($scopes === []) {
            return null;
        }
        $userRef = $this->tables->relation('user_notification', 'User_ID');
        $today = now()->toDateString();
        $ahead = now()->addDays(self::AHEAD_DAYS)->toDateString();
        $query = $this->tables->query('user_notification')->where('p.c_Seen', 0);

        return $query->where(function (Builder $any) use ($scopes, $userRef, $today, $ahead) {
            foreach ($scopes as $city => $scope) {
                $users = $scope['financial'] ? $scope['financialUsers'] : $scope['users'];
                $any->orWhere(fn (Builder $q) => $q->where('p.city_id', $city)->where('p.source_id', $scope['source'])->where('p.'.$userRef['state_column'], 'resolved')
                    ->whereIn('p.'.$userRef['resolved_column'], $users === [] ? [0] : $users)->where('p.c_Date', '<=', $scope['financial'] ? $today : $ahead));
            }
        });
    }
}
