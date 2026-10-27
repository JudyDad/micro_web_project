<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Services\Operational\OperationalTables;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The multi-select filters of the desktop search panel (fund type, project type, donor, parish, age range...), shared by
 * the application list and the statistics. Options are catalog entries merged across cities by name; a selected name
 * matches the catalog ids of every city that has it, so one choice works over a multi-city scope.
 */
final class ApplicationFilters
{
    /** key => [kind, source table or column, label key]. `ref` is a reference on the application, `person` on the beneficiary. */
    public const MULTI = [
        'fund_type' => ['ref', 'MP_FundType_ID', 'fundtype'], 'project_type' => ['ref', 'MP_Type_ID', 'microprojecttype'],
        'sub_type' => ['ref', 'MP_SubType_ID', 'microprojectsubtype'], 'partnership' => ['value', 'c_Partnership', null],
        'donor' => ['ref', 'MP_Donor', 'donor'], 'donor_group' => ['ref', 'DonorGroup_ID', 'donorgroup'],
        'category' => ['ref', 'Category_ID', 'category'], 'sub_category' => ['ref', 'SubCategory_ID', 'subcategory'],
        'career' => ['ref', 'Career_ID', 'career'], 'project_street' => ['ref', 'MP_Street_ID', 'street'],
        'parish' => ['person', 'P_Parish_ID', 'parish'], 'marital_status' => ['person', 'P_MaritalStatus_ID', 'maritalstatus'],
        'home_street' => ['person', 'Street_ID', 'street'], 'gender' => ['personvalue', 'c_P_Sex', null], 'age' => ['age', null, null],
    ];

    public const AGES = ['18-25' => [18, 25], '26-35' => [26, 35], '36-45' => [36, 45], '46-55' => [46, 55], '56-65' => [56, 65], '66+' => [66, 150]];

    public const RANGES = ['date_from', 'date_to', 'fund_from', 'fund_to', 'amount_from', 'amount_to', 'fund_amount_from', 'fund_amount_to'];

    public function __construct(private OperationalTables $tables) {}

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::MULTI);
    }

    /** Options per filter key: name => [en, ar, ids per city] (ids empty for value filters). */
    public function options(array $cityIds): array
    {
        $out = [];
        foreach (self::MULTI as $key => [$kind, $column, $table]) {
            $out[$key] = match ($kind) {
                'age' => collect(self::AGES)->mapWithKeys(fn ($range, $name) => [$name => ['en' => $name, 'ar' => $name, 'ids' => []]])->all(),
                'personvalue' => $this->distinct('person', $column, $cityIds),
                'value' => $this->distinct('microproject', $column, $cityIds),
                default => $this->catalogOptions($table, $cityIds),
            };
        }

        return $out;
    }

    private function catalogOptions(string $table, array $cityIds): array
    {
        $options = [];
        foreach ($cityIds as $city) {
            $source = DB::table('sources')->where('city_id', $city)->value('id');
            if ($source === null) {
                continue;
            }
            foreach ($this->tables->catalog($table, (int) $city, (int) $source) as $row) {
                $ar = trim((string) ($row->c_Name ?? $row->c_Name_ar ?? ''));
                $en = trim((string) ($row->c_Name_en ?? '')) ?: $ar;
                $ar = $ar ?: $en;
                if ($en === '') {
                    continue;
                }
                $options[$en] ??= ['en' => $en, 'ar' => $ar, 'ids' => []];
                $options[$en]['ids'][(int) $city][] = (int) $row->id;
            }
        }
        ksort($options);

        return $options;
    }

    private function distinct(string $table, string $column, array $cityIds): array
    {
        $values = $this->tables->query($table)->whereIn('p.city_id', $cityIds)->whereNotNull('p.'.$column)->where('p.'.$column, '!=', '')->distinct()->orderBy('p.'.$column)->pluck('p.'.$column);

        return $values->mapWithKeys(fn ($v) => [(string) $v => ['en' => (string) $v, 'ar' => (string) $v, 'ids' => []]])->all();
    }

    /** Adds every multi-select, fund and amount filter to a builder whose application rows are aliased `p`. */
    public function apply(Builder $query, array $cityIds, array $filters): Builder
    {
        $options = null;
        foreach (self::MULTI as $key => [$kind, $column, $table]) {
            $selected = array_values(array_filter((array) ($filters[$key] ?? []), fn ($v) => $v !== ''));
            if ($selected === []) {
                continue;
            }
            $options ??= $this->options($cityIds);
            if ($kind === 'value') {
                $query->whereIn('p.'.$column, $selected);
            } elseif ($kind === 'ref') {
                $this->refs($query, $this->idsByCity($options[$key], $selected), 'p', 'microproject', $column);
            } else {
                $query->whereExists($this->beneficiary($key, $kind, $column, $selected, $options[$key] ?? []));
            }
        }
        $this->ranges($query, $filters);

        return $query;
    }

    private function idsByCity(array $options, array $selected): array
    {
        $ids = [];
        foreach ($selected as $name) {
            foreach ($options[$name]['ids'] ?? [] as $city => $list) {
                $ids[$city] = array_merge($ids[$city] ?? [], $list);
            }
        }

        return $ids;
    }

    /** Restricts `$alias.<reference column of $column>` to the given catalog ids, per city. */
    private function refs(Builder $query, array $idsByCity, string $alias, string $table, string $column): void
    {
        $r = $this->tables->relation($table, $column);
        $query->where(function (Builder $outer) use ($idsByCity, $alias, $r) {
            $outer->whereRaw('1 = 0');
            foreach ($idsByCity as $city => $ids) {
                $outer->orWhere(fn (Builder $q) => $q->where($alias.'.city_id', $city)->where($alias.'.'.$r['state_column'], 'resolved')->whereIn($alias.'.'.$r['resolved_column'], $ids));
            }
        });
    }

    /** Subquery over the application's beneficiary (the linked person) with the person-level condition applied. */
    private function beneficiary(string $key, string $kind, ?string $column, array $selected, array $options): \Closure
    {
        $link = $this->tables->table('person_microproject')['name'];
        $person = $this->tables->table('person')['name'];
        $application = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');

        return function (Builder $q) use ($kind, $column, $selected, $options, $link, $person, $application, $personRef) {
            $q->selectRaw('1')->from($link.' as l')->join($person.' as pe', fn ($j) => $j->on('pe.id', '=', 'l.'.$personRef['resolved_column'])->on('pe.city_id', '=', 'l.city_id')->on('pe.source_id', '=', 'l.source_id'))
                ->whereColumn('l.city_id', 'p.city_id')->whereColumn('l.source_id', 'p.source_id')->whereColumn('l.'.$application['resolved_column'], 'p.id')
                ->where('l.'.$application['state_column'], 'resolved')->where('l.'.$personRef['state_column'], 'resolved')->where('l.c_PersonType', 'مستفيد');
            if ($kind === 'personvalue') {
                $q->whereIn('pe.'.$column, $selected);
            } elseif ($kind === 'age') {
                $q->where(function (Builder $ages) use ($selected) {
                    $ages->whereRaw('1 = 0');
                    foreach ($selected as $name) {
                        if (isset(self::AGES[$name])) {
                            [$min, $max] = self::AGES[$name];
                            $ages->orWhereBetween('pe.c_P_DOB', [now()->subYears($max + 1)->addDay()->format('Y-m-d'), now()->subYears($min)->format('Y-m-d')]);
                        }
                    }
                });
            } else {
                $this->refs($q, $this->idsByCity($options, $selected), 'pe', 'person', $column);
            }
        };
    }

    private function ranges(Builder $query, array $filters): void
    {
        $has = fn (string $k) => ($filters[$k] ?? '') !== '';
        if ($has('amount_from')) {
            $query->whereRaw('cast(p.c_MP_RequestedAmount as decimal(20,4)) >= ?', [$filters['amount_from']]);
        }
        if ($has('amount_to')) {
            $query->whereRaw('cast(p.c_MP_RequestedAmount as decimal(20,4)) <= ?', [$filters['amount_to']]);
        }
        if (! $has('fund_from') && ! $has('fund_to') && ! $has('fund_amount_from') && ! $has('fund_amount_to')) {
            return;
        }
        $loan = $this->tables->table('loan')['name'];
        $application = $this->tables->relation('loan', 'MicroProject_ID');
        $query->whereExists(function (Builder $q) use ($loan, $application, $filters, $has) {
            $q->selectRaw('1')->from($loan.' as ln')->whereColumn('ln.city_id', 'p.city_id')->whereColumn('ln.source_id', 'p.source_id')
                ->whereColumn('ln.'.$application['resolved_column'], 'p.id')->where('ln.'.$application['state_column'], 'resolved');
            foreach (['fund_from' => ['c_Loan_DateTaken', '>='], 'fund_to' => ['c_Loan_DateTaken', '<=']] as $k => [$col, $op]) {
                if ($has($k)) {
                    $q->where('ln.'.$col, $op, $filters[$k]);
                }
            }
            foreach (['fund_amount_from' => '>=', 'fund_amount_to' => '<='] as $k => $op) {
                if ($has($k)) {
                    $q->whereRaw('cast(ln.c_Loan_Amount as decimal(20,4)) '.$op.' ?', [$filters[$k]]);
                }
            }
        });
    }
}
