<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Operational\OperationalTables;
use App\Services\Staff\NoticeService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The visit schedule (`Visits_Schedule_Form`): the shared visit and clearance notices of the permitted cities with the desktop's actions
 * (hide / restore a notice, edit its note) and rescheduling: a not-yet-seen visit gets a new date and a reason (the original date is kept),
 * optionally moving the project's later unseen visits by the same number of days.
 */
final class VisitScheduleService
{
    public const PER_PAGE = 50;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private NoticeService $notices) {}

    /** Cities where the account may read visits. */
    public function cities(WebAccount $account): Collection
    {
        $view = $this->access->cities($account, 'applications.view')->pluck('id')->all();

        return $this->access->cities($account, 'visits.view')->filter(fn ($city) => in_array($city->id, $view, true))->values();
    }

    /**
     * @param  array{cities?: list<int>, year?: ?int, months?: list<int>, kind?: ?string, number?: ?string, seen?: ?string}  $filters
     * @return array{rows: LengthAwarePaginator, cities: Collection, manageCities: list<int>}
     */
    public function schedule(WebAccount $account, array $filters): array
    {
        [$query, $permitted] = $this->scheduleQuery($account, $filters, 'visits.schedule');

        $rows = $query->orderBy('n.notice_date')->orderBy('n.id')->paginate(self::PER_PAGE)->withQueryString();
        $status = $this->status($rows->getCollection());
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($status) {
            $row->visit_status = $status[(int) $row->id] ?? null;

            return $row;
        }));
        $manage = $permitted->filter(fn ($city) => $this->access->check($account, (int) $city->id, 'visits.manage'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        return ['rows' => $rows, 'cities' => $permitted, 'manageCities' => $manage];
    }

    /** @return array{0: Builder, 1: Collection} the filtered shared visit notices and the permitted cities */
    private function scheduleQuery(WebAccount $account, array $filters, string $event, bool $export = false): array
    {
        $permitted = $this->cities($account);
        if ($export) {
            // An export needs its own grant in each city, on top of the page's: a city without it is left out (and none left is a refusal).
            $permitted = $permitted->filter(fn ($city) => $this->access->check($account, (int) $city->id, 'reports.export'))->values();
        }
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $this->audit->record($account->id, $event, 'succeeded');

        $query = DB::table('web_notices as n')->join('cities as c', 'c.id', '=', 'n.city_id')->leftJoin('op_microproject as m', 'm.id', '=', 'n.project_id')
            ->whereIn('n.city_id', $ids)->whereIn('n.kind', NoticeService::SHARED)->whereNull('n.account_id')
            ->select(['n.*', 'c.name_en as city_name_en', 'c.name_ar as city_name_ar', 'm.c_MP_Name as project_name']);

        if (! empty($filters['year'])) {
            $query->whereBetween('n.notice_date', [sprintf('%04d-01-01', $filters['year']), sprintf('%04d-12-31', $filters['year'])]);
        }
        if (! empty($filters['months'])) {
            $months = array_map('intval', $filters['months']);
            $expression = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%m', n.notice_date) AS INTEGER)" : 'MONTH(n.notice_date)';
            $query->whereRaw($expression.' in ('.implode(',', array_fill(0, count($months), '?')).')', $months);
        }
        if (in_array($filters['kind'] ?? '', ['V', 'O'], true)) {
            $query->where('n.body', 'like', '%-'.$filters['kind']);
        }
        if (! empty($filters['number'])) {
            $query->where('n.body', 'like', 'Visit '.$filters['number'].'%'); // the validated names Opening, M1–M5 or Closing
        }
        if (in_array($filters['seen'] ?? '', ['0', '1'], true)) {
            $query->where('n.seen', (int) $filters['seen']);
        }

        return [$query, $permitted];
    }

    /**
     * Every visit of the schedule's filters (not one page) for the Excel/CSV export, in schedule order, with the desktop's visit status.
     * Authorization and the audit record happen here, before the first byte is sent.
     *
     * @return \Generator<int, list<string|int|null>>
     */
    public function exportRows(WebAccount $account, array $filters): \Generator
    {
        [$query] = $this->scheduleQuery($account, $filters, 'reports.exported', true);
        $query->orderBy('n.notice_date')->orderBy('n.id');

        return (function () use ($query) {
            foreach ($query->lazy(500)->chunk(500) as $chunk) {
                $rows = collect($chunk->all());
                $status = $this->status($rows);
                foreach ($rows as $row) {
                    yield [
                        $row->city_name_en, $row->project_number, $row->project_name, $row->title, $row->body, $row->notice_date,
                        (int) $row->seen === 1 ? 'Seen' : 'Not seen',
                        match ($status[(int) $row->id] ?? null) {
                            'done' => 'Done', 'na' => 'N/A', default => ''
                        }, $row->note,
                    ];
                }
            }
        })();
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
            $body = mb_strtolower((string) $row->body);
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

    /** Hides (seen = 1) or restores (0) a shared notice, as the desktop's schedule delete / restore. */
    public function setSeen(WebAccount $account, int $noticeId, int $seen): void
    {
        $notice = $this->manageable($account, $noticeId);
        DB::transaction(function () use ($account, $notice, $seen) {
            $this->notices->setSeenLike($notice, $seen);
            $this->audit->record($account->id, 'notifications.seen', 'succeeded', (int) $notice->city_id);
        });
    }

    /** Replaces the note of a shared notice (`Update_Notification_Note`); the text is stored as typed. */
    public function setNote(WebAccount $account, int $noticeId, string $note): void
    {
        $notice = $this->manageable($account, $noticeId);
        abort_if(strlen($note) > 65535, 422, 'The note is too long.');
        DB::transaction(function () use ($account, $notice, $note) {
            DB::table('web_notices')->where('id', $notice->id)->update(['note' => $note]);
            $this->audit->record($account->id, 'notifications.noted', 'succeeded', (int) $notice->city_id);
        });
    }

    /**
     * Gives a not-yet-seen visit a new date and a reason, keeping its original date. With `$shiftLater` the project's later unseen visits move
     * by the same number of days. A seen visit cannot be moved. Returns how many notices changed.
     */
    public function reschedule(WebAccount $account, int $noticeId, string $date, string $reason, bool $shiftLater): int
    {
        $notice = $this->manageable($account, $noticeId);
        $fail = fn (string $field, string $key): never => throw ValidationException::withMessages([$field => __('field.schedule.errors.'.$key)]);
        $notice->kind === NoticeService::VISIT || $fail('date', 'kind');
        (int) $notice->seen === 0 || $fail('date', 'seen');
        Fields::isDate($date) || $fail('date', 'date');
        $reason = trim($reason);
        ($reason !== '' && strlen($reason) <= 65535) || $fail('reason', 'reason');
        $days = (int) CarbonImmutable::parse(substr((string) $notice->notice_date, 0, 10))->diffInDays(CarbonImmutable::parse($date), false);
        $days !== 0 || $fail('date', 'same');

        return DB::transaction(function () use ($account, $notice, $days, $reason, $shiftLater) {
            $targets = collect([$notice]);
            if ($shiftLater) {
                $targets = $targets->concat(DB::table('web_notices')->where('city_id', $notice->city_id)->where('project_id', $notice->project_id)->where('kind', NoticeService::VISIT)
                    ->whereNull('account_id')->where('seen', 0)->where('id', '<>', $notice->id)
                    ->where(fn ($q) => $q->where('notice_date', '>', $notice->notice_date)->orWhere(fn ($same) => $same->where('notice_date', $notice->notice_date)->where('id', '>', $notice->id)))
                    ->orderBy('notice_date')->orderBy('id')->get());
            }
            foreach ($targets as $target) {
                $moved = CarbonImmutable::parse(substr((string) $target->notice_date, 0, 10))->addDays($days)->toDateString();
                DB::table('web_notices')->where('id', $target->id)->update([
                    'original_date' => $target->original_date ?? substr((string) $target->notice_date, 0, 10), 'notice_date' => $moved, 'change_reason' => $reason,
                ]);
            }
            $this->audit->record($account->id, 'notifications.rescheduled', 'succeeded', (int) $notice->city_id);

            return $targets->count();
        });
    }

    /** The stored shared notice when the account may manage visits of its city. */
    private function manageable(WebAccount $account, int $noticeId): object
    {
        $notice = DB::table('web_notices')->where('id', $noticeId)->whereIn('kind', NoticeService::SHARED)->whereNull('account_id')->first();
        abort_if($notice === null, 404);
        foreach (['applications.view', 'visits.view', 'visits.manage'] as $capability) {
            if (! $this->access->check($account, (int) $notice->city_id, $capability)) {
                $this->audit->record($account->id, 'access.denied', 'denied');
                abort(403);
            }
        }

        return $notice;
    }
}
