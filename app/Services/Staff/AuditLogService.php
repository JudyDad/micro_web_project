<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The web audit trail for administrators (the desktop's Log form). An administrator holds `accounts.manage` in some cities and sees only
 * those cities' events: events recorded for one of those cities, and events with no city (sign-ins, password changes) whose actor or
 * subject is an account with a capability or role in one of those cities. The audit rows themselves are append-only; this only reads them.
 */
final class AuditLogService
{
    public const PER_PAGE = 50;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit) {}

    /** Cities whose audit trail the account may read. */
    public function cities(WebAccount $account): Collection
    {
        return $this->access->cities($account, 'accounts.manage');
    }

    /**
     * @param  array{cities?: list<int>, from?: ?string, to?: ?string, event?: ?string, outcome?: ?string, actor?: ?string}  $filters
     * @return array{rows: LengthAwarePaginator, cities: Collection, events: list<string>}
     */
    public function page(WebAccount $account, array $filters): array
    {
        [$query, $permitted] = $this->prepare($account, $filters, 'logs.listed');
        $rows = $query->orderByDesc('e.id')->paginate(self::PER_PAGE)->withQueryString();
        $rows->setCollection(collect($this->lines($rows->getCollection())));

        return ['rows' => $rows, 'cities' => $permitted, 'events' => DB::table('account_audit_events')->distinct()->orderBy('event_type')->pluck('event_type')->all()];
    }

    /**
     * Every matching event (not one page) for the Excel/CSV export, newest first. Authorization and the audit record happen here, before the
     * first byte is sent.
     *
     * @return Generator<int, list<string|int|null>>
     */
    public function exportRows(WebAccount $account, array $filters): Generator
    {
        [$query] = $this->prepare($account, $filters, 'reports.exported');
        $query = $query->orderByDesc('e.id');

        return (function () use ($query) {
            foreach ($query->lazy(1000)->chunk(1000) as $chunk) {
                yield from $this->lines(collect($chunk->all()));
            }
        })();
    }

    public static function headings(): array
    {
        return ['Event number', 'Time (UTC)', 'Event', 'Outcome', 'Actor', 'City', 'Subject type', 'Subject number', 'Subject account'];
    }

    /** @return array{0: Builder, 1: Collection} */
    private function prepare(WebAccount $account, array $filters, string $event): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect(array_map('intval', $filters['cities'] ?? []), $permitted->pluck('id')->map(fn ($id) => (int) $id)->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->audit->record($account->id, $event, 'succeeded');

        $staff = fn (Builder $q) => $q->select('account_id')->from('account_city_capabilities')->whereIn('city_id', $ids)->whereNull('revoked_at')
            ->union(DB::table('account_city_roles')->select('account_id')->whereIn('city_id', $ids)->whereNull('revoked_at'));
        $query = DB::table('account_audit_events as e')->leftJoin('web_accounts as actor', 'actor.id', '=', 'e.actor_account_id')->leftJoin('web_accounts as subject', 'subject.id', '=', 'e.subject_account_id')
            ->leftJoin('cities as c', 'c.id', '=', 'e.city_id')
            ->select(['e.id', 'e.occurred_at', 'e.event_type', 'e.outcome', 'actor.username as actor_name', 'c.name_en as city_name', 'e.subject_type', 'e.subject_id', 'subject.username as subject_name'])
            ->where(function (Builder $scope) use ($ids, $staff) {
                $scope->whereIn('e.city_id', $ids)->orWhere(function (Builder $global) use ($staff) {
                    $global->whereNull('e.city_id')->where(function (Builder $who) use ($staff) {
                        $who->whereIn('e.actor_account_id', fn (Builder $q) => $staff($q))->orWhereIn('e.subject_account_id', fn (Builder $q) => $staff($q));
                    });
                });
            });
        if (($filters['from'] ?? '') !== '') {
            $query->where('e.occurred_at', '>=', $filters['from'].' 00:00:00');
        }
        if (($filters['to'] ?? '') !== '') {
            $query->where('e.occurred_at', '<', date('Y-m-d', strtotime($filters['to'].' +1 day')).' 00:00:00');
        }
        if (($filters['event'] ?? '') !== '') {
            $query->where('e.event_type', $filters['event']);
        }
        if (($filters['outcome'] ?? '') !== '') {
            $query->where('e.outcome', $filters['outcome']);
        }
        if (($filters['actor'] ?? '') !== '') {
            $query->where('actor.username', 'like', '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($filters['actor'], 0, 100)).'%');
        }

        return [$query, $permitted];
    }

    /** @return list<list<string|int|null>> */
    private function lines(Collection $rows): array
    {
        return $rows->map(fn ($row) => [
            (int) $row->id, substr((string) $row->occurred_at, 0, 19), $row->event_type, $row->outcome, $row->actor_name, $row->city_name, $row->subject_type, $row->subject_id, $row->subject_name,
        ])->all();
    }
}
