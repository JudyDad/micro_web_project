<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\Operational\OperationalTables;
use App\Services\SourceIdentity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Internal notices (`user_notification`, `UserNotification.cs`). A notice addresses a historical source user (`User_ID`); a web
 * account sees the notices of the source users it is explicitly linked to (reviewed legacy links), so recipients are resolved
 * through those links and never guessed from names. Nothing is delivered outside the application: there is no e-mail or push
 * channel, which is why test environments cannot deliver anything.
 */
final class NotificationService
{
    /** `Pay_ID` discriminators the desktop gives notices (the Phase 3 notification contract). */
    public const PURCHASES_DONE = -3;

    public const VISIT = -21;

    public const MESSAGE = -100;

    public function __construct(private OperationalTables $tables, private SourceIdentity $identity) {}

    /**
     * Source users of one city source who receive a kind of notice, as the desktop's `Select.cs` lists them.
     *
     * @return Collection<int, object{record_id: int, UserID: int, UserName: string}>
     */
    public function recipients(int $source, string $group): Collection
    {
        $query = $this->identity->publishedUsers($source);
        match ($group) {
            'communication' => $query->where('u.IsCommunication', 1),
            'financial' => $query->where(fn ($q) => $q->where('u.UserRoleID', 3)->orWhere('u.IsCashier', 1)),
            'lawful' => $query->where('u.UserRoleID', 7),
            'all' => $query->where('u.IsVisitor', '<>', -1), // select_users: IsVisitor != -1
            'me' => $query->where('u.IsME', 1),
            'staff' => $query->whereNotIn('u.UserRoleID', [4, 6]), // every role except Guest and Out of Service
            default => throw new InvalidArgumentException('Unknown recipient group.'),
        };

        return $query->orderBy('u.UserID')->get(['r.id as record_id', 'u.UserID', 'u.UserName'])
            ->map(fn ($row) => (object) ['record_id' => (int) $row->record_id, 'UserID' => (int) $row->UserID, 'UserName' => (string) $row->UserName]);
    }

    /**
     * The source user an account acts as in one source: its single active reviewed link there. An account without a link, or with
     * several, has no source identity; the audit trail records the account either way.
     *
     * @return ?object{record_id: int, UserID: int, UserName: string}
     */
    public function senderFor(WebAccount $account, int $source): ?object
    {
        $records = $this->linkedRecords($account, $source);
        if (count($records) !== 1) {
            return null;
        }
        $user = $this->identity->publishedUsers($source)->where('r.id', $records[0])->first(['r.id as record_id', 'u.UserID', 'u.UserName']);

        return $user === null ? null : (object) ['record_id' => (int) $user->record_id, 'UserID' => (int) $user->UserID, 'UserName' => (string) $user->UserName];
    }

    /**
     * Source-user records of one source the account is linked to (non-revoked link, principal not disabled).
     *
     * @return list<int>
     */
    public function linkedRecords(WebAccount $account, int $source): array
    {
        return DB::table('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')
            ->where('l.account_id', $account->id)->whereNull('l.revoked_at')->whereNull('wp.disabled_at')->where('l.source_id', $source)->where('wp.source_id', $source)
            ->orderBy('wp.source_record_id')->pluck('wp.source_record_id')->map(fn ($id) => (int) $id)->all();
    }

    /** Stores one unseen notice for a source user (none: the shared copy of a visit notice), sent by the given source user when the sender has one. */
    public function add(int $city, int $source, ?object $project, string $date, string $body, string $name, int $payId, ?object $recipient, ?object $sender = null): int
    {
        $payload = [
            'c_Body' => $body, 'c_Date' => $date, 'c_P_Name' => $name, 'c_Pay_ID' => $payId, 'c_Seen' => 0,
            'c_MicroProject_ID' => $project?->c_MP_ID, 'c_User_ID' => $recipient?->UserID, 'c_Sender_ID' => $sender?->UserID,
        ] + $this->tables->link('user_notification', 'User_ID', $recipient?->record_id)
            + $this->tables->link('user_notification', 'Sender_ID', $sender?->record_id)
            + $this->tables->link('user_notification', 'MicroProject_ID', $project === null ? null : (int) $project->id);

        return $this->tables->insert('user_notification', $city, $source, $payload);
    }

    /**
     * Marks seen (1) or unseen (0) the project's notices of one kind: the desktop's `Update_NotificationWithPaymentID(-3, …)` without a project
     * condition cleared every project's notices, so the web narrows it to the project that changed.
     */
    public function setSeenForProject(object $project, int $payId, int $seen): int
    {
        $ref = $this->tables->relation('user_notification', 'MicroProject_ID');

        return DB::table('op_user_notification')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($ref['resolved_column'], $project->id)->where($ref['state_column'], 'resolved')->where('c_Pay_ID', $payId)->update(['c_Seen' => $seen]);
    }

    /**
     * Marks seen or unseen every copy of a notice (the desktop's `Update_MicroUsers_Notification`): same city source, project, date,
     * text, title and kind, whoever it is addressed to. `$row` is an `op_user_notification` row.
     */
    public function setSeenLike(object $row, int $seen): int
    {
        $query = DB::table('op_user_notification')->where('city_id', $row->city_id)->where('source_id', $row->source_id);
        foreach (['c_MicroProject_ID', 'c_Date', 'c_Body', 'c_P_Name', 'c_Pay_ID'] as $column) {
            $row->{$column} === null ? $query->whereNull($column) : $query->where($column, $row->{$column});
        }

        return $query->update(['c_Seen' => $seen]);
    }

    /** Marks seen every notice of one payment (`Update_NotificationWithPaymentID`, the financial users' shared inbox). */
    public function setSeenForPayment(object $row, int $seen): int
    {
        return DB::table('op_user_notification')->where('city_id', $row->city_id)->where('source_id', $row->source_id)->where('c_Pay_ID', $row->c_Pay_ID)->update(['c_Seen' => $seen]);
    }
}
