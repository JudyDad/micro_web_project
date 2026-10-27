<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * City-aware application search filters and permitted cross-city counts (P6.1).
 * Counts are plain record counts: no money is summed here because the requested amount carries no currency.
 */
final class ApplicationReport
{
    public const STAGES = ['zero', 'project'];

    public function __construct(private OperationalTables $tables, private AccountAccess $access, private AccountAuditTrail $audit) {}

    /** Permitted city ids for an explicit selection; an unpermitted city is refused, an empty selection means every permitted city. */
    public function cityScope(WebAccount $account, array $selected): array
    {
        abort_unless($this->access->active($account), 403);
        $allowed = $this->access->cities($account, 'applications.view')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selected = array_values(array_unique(array_map('intval', $selected)));
        if (array_diff($selected, $allowed) !== []) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }

        return $selected === [] ? $allowed : $selected;
    }

    /** Distinct lifecycle state names across the given cities, keyed by English name: en, ar and the catalog ids per city. */
    public function stateOptions(array $cityIds): Collection
    {
        $options = [];
        foreach ($cityIds as $city) {
            $source = DB::table('sources')->where('city_id', $city)->value('id');
            if ($source === null) {
                continue;
            }
            foreach ($this->tables->catalog('state', (int) $city, (int) $source) as $state) {
                $name = (string) $state->c_Name_en;
                $options[$name] ??= ['en' => $name, 'ar' => (string) $state->c_Name_ar, 'ids' => []];
                $options[$name]['ids'][(int) $city][] = (int) $state->id;
            }
        }
        ksort($options);

        return collect($options);
    }

    /** Adds the stage, lifecycle-state and request-date filters to a builder whose application rows are aliased `p`. */
    public function applyFilters(Builder $query, array $cityIds, array $filters): Builder
    {
        if (($filters['stage'] ?? '') !== '') {
            $query->where('p.c_ApplicationState', $filters['stage']);
        }
        if (($filters['date_from'] ?? '') !== '') {
            $query->where('p.c_MP_DateOfRequest', '>=', $filters['date_from']);
        }
        if (($filters['date_to'] ?? '') !== '') {
            $query->where('p.c_MP_DateOfRequest', '<=', $filters['date_to']);
        }
        $selectedStates = array_values(array_filter((array) ($filters['state'] ?? []), fn ($v) => $v !== ''));
        if ($selectedStates !== []) {
            $options = $this->stateOptions($cityIds);
            $byCity = [];
            foreach ($selectedStates as $name) {
                foreach ($options->get($name)['ids'] ?? [] as $city => $stateIds) {
                    $byCity[$city] = array_merge($byCity[$city] ?? [], $stateIds);
                }
            }
            $query->where(function (Builder $outer) use ($byCity) {
                $outer->whereRaw('1 = 0');
                foreach ($byCity as $city => $stateIds) {
                    $marks = implode(',', array_fill(0, count($stateIds), '?'));
                    $outer->orWhereRaw('(p.city_id = ? and ('.$this->currentStateSql().') in ('.$marks.'))', [$city, ...$stateIds]);
                }
            });
        }
        app(ApplicationFilters::class)->apply($query, $cityIds, $filters);

        return $query;
    }

    /** Correlated SQL selecting the state catalog id of an application's current lifecycle row (flagged current, else newest). */
    public function currentStateSql(): string
    {
        $application = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $state = $this->tables->relation('microproject_state', 'State_ID');
        $table = $this->tables->table('microproject_state')['name'];

        return 'select ms.'.$state['resolved_column'].' from '.$table.' ms'
            .' where ms.'.$application['resolved_column'].' = p.id and ms.'.$application['state_column']." = 'resolved'"
            .' and ms.city_id = p.city_id and ms.source_id = p.source_id and ms.'.$state['state_column']." = 'resolved'"
            .' order by case when ms.c_Current_State = 1 then 0 else 1 end, ms.c_Date desc, ms.id desc limit 1';
    }

    /** Counts over the permitted cities, with per-city coverage so a city left out of the numbers is visible. */
    public function statistics(WebAccount $account, array $selectedCities, array $filters): array
    {
        $cityIds = $this->cityScope($account, $selectedCities);
        $permitted = $this->access->cities($account, 'applications.view');
        $inner = $this->applyFilters($this->tables->query('microproject'), $cityIds, $filters)->whereIn('p.city_id', $cityIds)
            ->select([])->selectRaw('p.city_id as city, p.c_ApplicationState as stage, substr(p.c_MP_DateOfRequest, 1, 4) as year, ('.$this->currentStateSql().') as state_id');
        $rows = DB::query()->fromSub($inner, 'a')->selectRaw('city, stage, year, state_id, count(*) as total')->groupBy('city', 'stage', 'year', 'state_id')->get();

        $names = [];
        foreach ($this->stateOptions($cityIds) as $entry) {
            foreach ($entry['ids'] as $city => $ids) {
                foreach ($ids as $id) {
                    $names[$city][$id] = $entry['en'];
                }
            }
        }
        $byCity = array_fill_keys($cityIds, 0);
        $byState = $byYear = $matrix = [];
        $byStage = ['zero' => 0, 'project' => 0, 'other' => 0];
        $total = $unrecordedState = $undated = 0;
        foreach ($rows as $row) {
            $count = (int) $row->total;
            $city = (int) $row->city;
            $state = $row->state_id === null ? '' : ($names[$city][(int) $row->state_id] ?? '');
            $total += $count;
            $byCity[$city] += $count;
            $byState[$state] = ($byState[$state] ?? 0) + $count;
            $matrix[$state][$city] = ($matrix[$state][$city] ?? 0) + $count;
            $byStage[in_array($row->stage, self::STAGES, true) ? $row->stage : 'other'] += $count;
            $unrecordedState += $state === '' ? $count : 0;
            if ($row->year === null || $row->year === '') {
                $undated += $count;
            } else {
                $byYear[(string) $row->year] = ($byYear[(string) $row->year] ?? 0) + $count;
            }
        }
        ksort($byState);
        ksort($byYear);
        $this->audit->record($account->id, 'reports.statistics', 'succeeded');

        return [
            'cities' => $cityIds, 'total' => $total, 'byCity' => $byCity, 'byState' => $byState, 'byStage' => $byStage, 'byYear' => $byYear,
            'matrix' => $matrix, 'labels' => $this->stateOptions($cityIds), 'unrecordedState' => $unrecordedState, 'undated' => $undated,
            'coverage' => $permitted->map(fn ($city) => (object) [
                'id' => (int) $city->id, 'name_en' => $city->name_en, 'name_ar' => $city->name_ar,
                'included' => in_array((int) $city->id, $cityIds, true), 'count' => $byCity[(int) $city->id] ?? 0,
            ]),
        ];
    }

    /**
     * The statistics as one flat table (section, category, city, records), including which cities were and were not covered,
     * so a workbook carries the same coverage statement as the page. Counts only, as on the page.
     *
     * @param  array<string, mixed>  $statistics  the result of statistics()
     * @return list<list<string|int>>
     */
    public function exportRows(array $statistics): array
    {
        $cityNames = $statistics['coverage']->keyBy('id');
        $rows = [['Total', 'All matching applications', '', $statistics['total']]];
        foreach ($statistics['coverage'] as $city) {
            $status = ! $city->included ? 'Not included' : ($city->count === 0 ? 'Included, no matching records' : 'Included');
            $rows[] = ['Coverage', $status, $city->name_en, $city->count];
        }
        foreach ($statistics['matrix'] as $state => $perCity) {
            foreach ($statistics['cities'] as $cityId) {
                $rows[] = ['Lifecycle status', $state === '' ? 'No status recorded' : (string) $state, $cityNames[$cityId]->name_en ?? (string) $cityId, $perCity[$cityId] ?? 0];
            }
        }
        foreach (['zero' => 'Preliminary', 'project' => 'Full', 'other' => 'Other'] as $stage => $label) {
            $rows[] = ['Application stage', $label, '', $statistics['byStage'][$stage]];
        }
        foreach ($statistics['byYear'] as $year => $count) {
            $rows[] = ['Request year', (string) $year, '', $count];
        }
        $rows[] = ['Request year', 'No request date', '', $statistics['undated']];

        return $rows;
    }
}
