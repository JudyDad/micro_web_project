<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** The instalment schedule across the permitted cities (Payments_Form): unpaid by due date, paid by received date, totals per currency. */
final class PaymentScheduleService
{
    public const PER_PAGE = 50;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    /** Cities where the account may read finance data. */
    public function cities(WebAccount $account): Collection
    {
        $view = $this->access->cities($account, 'applications.view')->pluck('id')->all();

        return $this->access->cities($account, 'finance.view')->filter(fn ($city) => in_array($city->id, $view, true))->values();
    }

    /**
     * @param  array{cities?: list<int>, state?: string, year?: ?int, months?: list<int>, group?: string}  $filters
     * @return array{rows: LengthAwarePaginator, totals: Collection, cities: Collection, groups: Collection}
     */
    public function schedule(WebAccount $account, array $filters): array
    {
        [$query, $ids, $dateColumn, $permitted] = $this->filtered($account, $filters);
        $this->audit->record($account->id, 'finance.list', 'succeeded');
        $totals = (clone $query)->reorder()->select(DB::raw('l.c_Currency as currency, COUNT(*) as payments, SUM(p.c_Pay_Amount) as total'))->groupBy('l.c_Currency')->orderBy('l.c_Currency')->get()
            ->map(fn ($row) => (object) ['currency' => $row->currency, 'payments' => (int) $row->payments, 'total' => Money::canonical($row->total) ?? '0']);
        $rows = $query->orderBy($dateColumn)->orderBy('p.id')->paginate(self::PER_PAGE)->withQueryString();
        $names = $this->names($rows->getCollection()->pluck('project_id')->unique()->all());
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($names) {
            $row->amount = Money::canonical($row->c_Pay_Amount ?? null) ?? '0';
            $row->beneficiary = $names[$row->project_id]['مستفيد'] ?? '';
            $row->partner = $names[$row->project_id]['شريك'] ?? '';

            return $row;
        }));

        return ['rows' => $rows, 'totals' => $totals, 'cities' => $permitted, 'groups' => $this->groups($ids)];
    }

    /** @return array{0: Builder, 1: list<int>, 2: string, 3: Collection} the filtered instalment query, its cities, the date column and the permitted cities */
    private function filtered(WebAccount $account, array $filters, bool $export = false): array
    {
        $permitted = $this->cities($account);
        if ($export) {
            // An export needs its own grant in each city, on top of the page's: a city without it is left out (and none left is a refusal).
            $permitted = $permitted->filter(fn ($city) => $this->access->check($account, (int) $city->id, 'finance.export'))->values();
        }
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $paid = ($filters['state'] ?? 'pending') === 'paid';
        $dateColumn = $paid ? 'p.c_Pay_RecievedOnDate' : 'p.c_Pay_DueDate';
        $query = $this->base($ids)->where('p.c_Pay_IsPaid', $paid ? FinanceService::PAID : FinanceService::NOT_PAID);
        if (! empty($filters['year'])) {
            $query->whereBetween($dateColumn, [sprintf('%04d-01-01', $filters['year']), sprintf('%04d-12-31', $filters['year'])]);
        }
        if (! empty($filters['months'])) {
            $months = array_map('intval', $filters['months']);
            $expression = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%m', {$dateColumn}) AS INTEGER)" : "MONTH({$dateColumn})";
            $query->whereRaw($expression.' in ('.implode(',', array_fill(0, count($months), '?')).')', $months);
        }
        if (! empty($filters['group'])) {
            $query->whereRaw('lower(g.c_Name) = lower(?)', [$filters['group']]);
        }

        return [$query, $ids, $dateColumn, $permitted];
    }

    /**
     * Every instalment matching the schedule's filters (not one page) for the CSV export, ordered as the schedule is.
     * Authorization and the audit record happen here, before the first byte is sent.
     *
     * @return \Generator<int, list<string|int|null>>
     */
    public function exportRows(WebAccount $account, array $filters): \Generator
    {
        [$query, , $dateColumn] = $this->filtered($account, $filters, true);
        $this->audit->record($account->id, 'reports.exported', 'succeeded');
        $cityNames = DB::table('cities')->pluck('name_en', 'id');
        $query->orderBy($dateColumn)->orderBy('p.id');

        return (function () use ($query, $cityNames) {
            foreach ($query->lazy(500)->chunk(500) as $chunk) {
                $names = $this->names($chunk->pluck('project_id')->unique()->all());
                foreach ($chunk as $row) {
                    $paid = $row->c_Pay_IsPaid === FinanceService::PAID;
                    yield [
                        $cityNames[$row->city_id] ?? '', $row->project_number, $names[$row->project_id]['مستفيد'] ?? '', $names[$row->project_id]['شريك'] ?? '',
                        $row->group_name, $row->currency, Money::canonical($row->c_Pay_Amount ?? null) ?? '0', Fields::day($row->c_Pay_DueDate ?? null),
                        $paid ? 'Paid' : 'Not paid', $paid ? Fields::day($row->c_Pay_RecievedOnDate ?? null) : '',
                    ];
                }
            }
        })();
    }

    /**
     * Type-ahead matches for opening a project's finance page, across the permitted cities: by project number, project name,
     * or the name of a linked beneficiary or partner. Every project is searchable; the first `$limit` matches are returned.
     *
     * @return Collection<int, object{id: int, label: string}>
     */
    public function lookupProjects(WebAccount $account, string $search, int $limit = 30): Collection
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $cityIds = $permitted->pluck('id')->map(fn ($id) => (int) $id)->all();
        $search = trim(mb_substr($search, 0, 200));
        $query = $this->tables->query('microproject')->whereIn('p.city_id', $cityIds);
        if ($search !== '') {
            $literal = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
            $personRef = $this->tables->relation('person_microproject', 'Person_ID');
            $query->where(function (Builder $q) use ($literal, $search, $projectRef, $personRef) {
                $q->whereRaw("p.c_MP_Name LIKE ? ESCAPE '!'", [$literal]);
                if (ctype_digit($search) && strlen($search) <= 10) {
                    // A typed number matches project numbers that start with it, so "100" already lists 1000, 1001, …
                    $cast = DB::connection()->getDriverName() === 'sqlite' ? 'CAST(p.c_MP_ID AS TEXT)' : 'CAST(p.c_MP_ID AS CHAR)';
                    $q->orWhereRaw("$cast LIKE ?", [$search.'%']);
                }
                $q->orWhereExists(function (Builder $link) use ($literal, $projectRef, $personRef) {
                    $link->selectRaw('1')->from('op_person_microproject as pm')
                        ->join('op_person as pp', fn ($j) => $j->on('pp.id', '=', 'pm.'.$personRef['resolved_column'])->on('pp.city_id', '=', 'pm.city_id')->on('pp.source_id', '=', 'pm.source_id'))
                        ->whereColumn('pm.'.$projectRef['resolved_column'], 'p.id')->whereColumn('pm.city_id', 'p.city_id')->whereColumn('pm.source_id', 'p.source_id')
                        ->where('pm.'.$personRef['state_column'], 'resolved')
                        ->whereIn('pm.id', $this->tables->query('person_microproject')->select('p.id'))
                        ->where(function (Builder $name) use ($literal) {
                            foreach (['c_P_FirstName', 'c_P_LastName', 'c_P_FatherName', 'c_P_NationalNumber'] as $field) {
                                $name->orWhereRaw("pp.$field LIKE ? ESCAPE '!'", [$literal]);
                            }
                            $name->orWhereRaw(self::fullName('pp')." LIKE ? ESCAPE '!'", [$literal]);
                        });
                });
            });
        }
        $rows = $query->orderBy('p.id')->limit(max(1, min($limit, 100)))->get();
        $names = $this->names($rows->pluck('id')->map(fn ($id) => (int) $id)->all());
        $ar = app()->isLocale('ar');

        return $rows->map(function ($row) use ($names, $ar) {
            $parts = array_filter([
                $row->c_MP_ID !== null ? '#'.$row->c_MP_ID : null, (string) ($row->c_MP_Name ?? ''),
                $names[(int) $row->id]['مستفيد'] ?? null, $ar ? $row->city_name_ar : $row->city_name_en,
            ], fn ($part) => $part !== null && $part !== '');

            return (object) ['id' => (int) $row->id, 'label' => implode(' · ', $parts)];
        });
    }

    /** SQL for "first last" of the person alias, so a typed full name matches too (NULL parts count as empty). */
    public static function fullName(string $alias): string
    {
        $first = "COALESCE($alias.c_P_FirstName, '')";
        $last = "COALESCE($alias.c_P_LastName, '')";

        return DB::connection()->getDriverName() === 'sqlite' ? "($first || ' ' || $last)" : "CONCAT($first, ' ', $last)";
    }

    private function base(array $cityIds): Builder
    {
        $loanRef = $this->tables->relation('payment', 'Loan_ID');
        $projectRef = $this->tables->relation('loan', 'MicroProject_ID');
        $groupRef = $this->tables->relation('microproject', 'DonorGroup_ID');
        $scope = fn (string $alias, string $parent) => fn ($join) => $join->on($alias.'.city_id', '=', $parent.'.city_id')->on($alias.'.source_id', '=', $parent.'.source_id');

        return $this->tables->query('payment')
            ->join('op_loan as l', fn ($j) => $scope('l', 'p')($j->on('l.id', '=', 'p.'.$loanRef['resolved_column'])))
            ->join('op_microproject as m', fn ($j) => $scope('m', 'l')($j->on('m.id', '=', 'l.'.$projectRef['resolved_column'])))
            ->leftJoin('op_donorgroup as g', fn ($j) => $scope('g', 'm')($j->on('g.id', '=', 'm.'.$groupRef['resolved_column'])))
            ->where('p.'.$loanRef['state_column'], 'resolved')->where('l.'.$projectRef['state_column'], 'resolved')
            ->whereIn('p.city_id', $cityIds === [] ? [0] : $cityIds)
            ->addSelect(['l.c_Currency as currency', 'm.c_MP_ID as project_number', 'm.id as project_id', 'g.c_Name as group_name']);
    }

    /** Distinct group names of the selected cities, for the filter. */
    private function groups(array $cityIds): Collection
    {
        return DB::table('op_donorgroup')->whereIn('city_id', $cityIds === [] ? [0] : $cityIds)->whereNotNull('c_Name')->distinct()->orderBy('c_Name')->pluck('c_Name');
    }

    /** @return array<int, array<string, string>> project id → person type → "first last ابن/ة father" */
    private function names(array $projectIds): array
    {
        if ($projectIds === []) {
            return [];
        }
        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->whereIn('p.'.$projectRef['resolved_column'], $projectIds)
            ->whereIn('p.c_PersonType', ['مستفيد', 'شريك'])->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
        $people = $this->tables->query('person')->whereIn('p.id', $links->pluck($personRef['resolved_column'])->unique()->all())->get()->keyBy('id');
        $result = [];
        foreach ($links as $link) {
            $person = $people->get((int) $link->{$personRef['resolved_column']});
            $project = (int) $link->{$projectRef['resolved_column']};
            if ($person === null || isset($result[$project][$link->c_PersonType])) {
                continue;
            }
            $result[$project][$link->c_PersonType] = trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '').' ابن/ة '.($person->c_P_FatherName ?? ''));
        }

        return $result;
    }
}
