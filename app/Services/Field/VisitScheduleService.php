<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The visit schedule (`Visits_Schedule_Form`, `UserNotification.Select_VisitNotifications`): read-only list of the visit notices
 * already stored (`Pay_ID` = -21, no recipient). Generating or marking notices belongs to the notification work (P5.3).
 */
final class VisitScheduleService
{
    public const PER_PAGE = 50;

    /** The discriminator the desktop gives visit notices (see the Phase 3 notification contract). */
    public const VISIT_NOTICE = -21;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    /** Cities where the account may read visits. */
    public function cities(WebAccount $account): Collection
    {
        $view = $this->access->cities($account, 'applications.view')->pluck('id')->all();

        return $this->access->cities($account, 'visits.view')->filter(fn ($city) => in_array($city->id, $view, true))->values();
    }

    /**
     * @param  array{cities?: list<int>, year?: ?int, months?: list<int>, kind?: ?string, number?: ?string, seen?: ?string}  $filters
     * @return array{rows: LengthAwarePaginator, cities: Collection}
     */
    public function schedule(WebAccount $account, array $filters): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $this->audit->record($account->id, 'visits.schedule', 'succeeded');

        $projectRef = $this->tables->relation('user_notification', 'MicroProject_ID');
        $query = $this->tables->query('user_notification')
            ->leftJoin('op_microproject as m', fn ($j) => $j->on('m.id', '=', 'p.'.$projectRef['resolved_column'])->on('m.city_id', '=', 'p.city_id')->on('m.source_id', '=', 'p.source_id')
                ->where('p.'.$projectRef['state_column'], '=', 'resolved'))
            ->addSelect(['m.id as project_id', 'm.c_MP_Name as project_name'])
            ->whereIn('p.city_id', $ids)->where('p.c_Pay_ID', self::VISIT_NOTICE)->whereNull('p.c_User_ID');

        if (! empty($filters['year'])) {
            $query->whereBetween('p.c_Date', [sprintf('%04d-01-01', $filters['year']), sprintf('%04d-12-31', $filters['year'])]);
        }
        if (! empty($filters['months'])) {
            $months = array_map('intval', $filters['months']);
            $expression = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%m', p.c_Date) AS INTEGER)" : 'MONTH(p.c_Date)';
            $query->whereRaw($expression.' in ('.implode(',', array_fill(0, count($months), '?')).')', $months);
        }
        if (in_array($filters['kind'] ?? '', ['V', 'O'], true)) {
            $query->where('p.c_Body', 'like', '%-'.$filters['kind']);
        }
        if (! empty($filters['number'])) {
            $query->where('p.c_Body', 'like', 'Visit '.$filters['number'].'%'); // the validated names Opening, M1–M5 or Closing
        }
        if (in_array($filters['seen'] ?? '', ['0', '1'], true)) {
            $query->where('p.c_Seen', (int) $filters['seen']);
        }

        $rows = $query->orderBy('p.c_Date')->orderBy('p.id')->paginate(self::PER_PAGE)->withQueryString();
        $status = $this->status($rows->getCollection());
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($status) {
            $row->visit_status = $status[(int) $row->id] ?? null;

            return $row;
        }));

        return ['rows' => $rows, 'cities' => $permitted];
    }

    /**
     * The desktop's "visit status" column: Done when the matching visit exists (opening/closing by kind, M1–M5 by number for the
     * project's people), "N/A" for other notice texts, empty when the visit is not found.
     *
     * @return array<int, ?string> notice id → 'done' | 'na' | null
     */
    private function status(Collection $rows): array
    {
        $projects = $rows->pluck('project_id')->filter()->unique()->values()->all();
        $kinds = [];
        $persons = [];
        $numbers = [];
        if ($projects !== []) {
            $visitRef = $this->tables->relation('visit', 'MicroProject_ID');
            foreach ($this->tables->query('visit')->whereIn('p.'.$visitRef['resolved_column'], $projects)->get() as $visit) {
                $kinds[(int) $visit->{$visitRef['resolved_column']}][] = (string) $visit->c_Kind;
            }
            $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
            $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
            foreach ($this->tables->query('person_microproject')->whereIn('p.'.$linkProject['resolved_column'], $projects)->where('p.'.$linkPerson['state_column'], 'resolved')
                ->get() as $link) {
                $persons[(int) $link->{$linkProject['resolved_column']}][] = (int) $link->{$linkPerson['resolved_column']};
            }
            $all = collect($persons)->flatten()->unique()->values()->all();
            if ($all !== []) {
                $meRef = $this->tables->relation('mevisit', 'Person_ID');
                foreach ($this->tables->query('mevisit')->whereIn('p.'.$meRef['resolved_column'], $all)->get() as $me) {
                    $numbers[(int) $me->{$meRef['resolved_column']}][(string) $me->c_Number] = true;
                }
            }
        }

        $result = [];
        foreach ($rows as $row) {
            $body = mb_strtolower((string) $row->c_Body);
            $project = (int) ($row->project_id ?? 0);
            if (str_contains($body, 'op') || str_contains($body, 'cl')) {
                $needle = str_contains($body, 'op') ? 'op' : 'cl';
                $found = collect($kinds[$project] ?? [])->contains(fn ($kind) => str_contains($kind, $needle));
            } elseif (preg_match('/m([1-5])/', $body, $match) === 1) {
                $found = collect($persons[$project] ?? [])->contains(fn ($person) => isset($numbers[$person][$match[1]]));
            } else {
                $result[(int) $row->id] = 'na';

                continue;
            }
            $result[(int) $row->id] = $found ? 'done' : null;
        }

        return $result;
    }
}
