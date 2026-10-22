<?php

declare(strict_types=1);

namespace App\Services\Staff;

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
            'all' => $query->where('u.UserRoleID', '<>', 6),
            default => throw new InvalidArgumentException('Unknown recipient group.'),
        };

        return $query->orderBy('u.UserID')->get(['r.id as record_id', 'u.UserID', 'u.UserName'])
            ->map(fn ($row) => (object) ['record_id' => (int) $row->record_id, 'UserID' => (int) $row->UserID, 'UserName' => (string) $row->UserName]);
    }

    /**
     * Stores one unseen notice for a source user. The sender is never recorded: the desktop's sender is a source user and the acting
     * web account is audited instead.
     */
    public function add(int $city, int $source, ?object $project, string $date, string $body, string $name, int $payId, object $recipient): int
    {
        $payload = [
            'c_Body' => $body, 'c_Date' => $date, 'c_P_Name' => $name, 'c_Pay_ID' => $payId, 'c_Seen' => 0,
            'c_MicroProject_ID' => $project?->c_MP_ID, 'c_User_ID' => $recipient->UserID,
        ] + $this->tables->link('user_notification', 'User_ID', $recipient->record_id)
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
}
