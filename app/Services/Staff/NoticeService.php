<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Services\AccessRoles;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Internal notices (`web_notices`). A notice addresses a web account of a city, or is the shared copy of a visit or clearance notice (no
 * recipient). Recipients come from the roles an administrator assigned in that city, so nothing is configured per user. Nothing is
 * delivered outside the application: there is no e-mail or push channel, which is why test environments cannot deliver anything.
 */
final class NoticeService
{
    public const VISIT = 'visit';

    public const CLEARANCE = 'clearance';

    public const PAYMENT = 'payment';

    public const TROPHY = 'trophy';

    public const EXECUTIVE_FILE = 'executive_file';

    public const STATE = 'state';

    public const PURCHASES = 'purchases';

    public const MESSAGE = 'message';

    /** Kinds that exist once per project and are listed on the visit schedule. */
    public const SHARED = [self::VISIT, self::CLEARANCE];

    public function __construct(private AccessRoles $roles) {}

    /**
     * Accounts that receive a kind of notice in a city: the enabled holders of the roles routed to it (`AccessRoles::RECIPIENTS`).
     *
     * @return Collection<int, int> account ids
     */
    public function recipients(int $city, string $group): Collection
    {
        $roles = AccessRoles::RECIPIENTS[$group] ?? throw new InvalidArgumentException('Unknown recipient group.');

        return $this->roles->holders($city, $roles);
    }

    /** Whether the city has an enabled account with the monitoring-and-evaluation duty (needed before visit notices are generated). */
    public function hasMonitoring(int $city): bool
    {
        return $this->roles->holders($city, ['monitoring'])->isNotEmpty();
    }

    /** Stores one unseen notice. `$account` null makes it the shared copy (only visit and clearance notices may be shared). */
    public function add(int $city, int $source, ?object $project, string $kind, string $date, string $title, string $body, ?int $account, ?int $sender = null, ?int $payKey = null): int
    {
        return (int) DB::table('web_notices')->insertGetId([
            'city_id' => $city, 'source_id' => $source, 'account_id' => $account, 'sender_account_id' => $sender, 'project_id' => $project?->id, 'project_number' => $project?->c_MP_ID,
            'kind' => $kind, 'pay_key' => $payKey, 'notice_date' => $date, 'original_date' => null, 'title' => $title, 'body' => $body, 'note' => null, 'change_reason' => null,
            'seen' => 0, 'created_at' => now('UTC')->format('Y-m-d H:i:s.u'),
        ]);
    }

    /** One notice per recipient account of the group; returns how many were stored. */
    public function broadcast(int $city, int $source, ?object $project, string $group, string $kind, string $date, string $title, string $body, ?int $sender, ?int $payKey = null): int
    {
        $accounts = $this->recipients($city, $group);
        foreach ($accounts as $account) {
            $this->add($city, $source, $project, $kind, $date, $title, $body, $account, $sender, $payKey);
        }

        return $accounts->count();
    }

    /** The project's notices (every recipient). */
    public function ofProject(object $project): Builder
    {
        return DB::table('web_notices')->where('city_id', $project->city_id)->where('project_id', $project->id);
    }

    /** Marks seen or unseen the project's notices of one kind (clearing "purchases finished" touches only the project that changed). */
    public function setSeenForProject(object $project, string $kind, int $seen): int
    {
        return $this->ofProject($project)->where('kind', $kind)->update(['seen' => $seen]);
    }

    /**
     * Marks seen or unseen every copy of a notice: same city, project, date, title, text and kind, whoever it addresses (the desktop cleared
     * a notice for all its recipients at once). `$row` is a `web_notices` row.
     */
    public function setSeenLike(object $row, int $seen): int
    {
        $query = DB::table('web_notices')->where('city_id', $row->city_id)->where('kind', $row->kind);
        foreach (['project_id', 'notice_date', 'title', 'body'] as $column) {
            $row->{$column} === null ? $query->whereNull($column) : $query->where($column, $row->{$column});
        }

        return $query->update(['seen' => $seen]);
    }

    /** Marks seen every notice of one payment (all financial recipients clear together). */
    public function setSeenForPayment(object $row, int $seen): int
    {
        return DB::table('web_notices')->where('city_id', $row->city_id)->where('kind', self::PAYMENT)->where('pay_key', $row->pay_key)->update(['seen' => $seen]);
    }
}
