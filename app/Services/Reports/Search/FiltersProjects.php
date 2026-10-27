<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;

/**
 * The project filters the desktop's `Check_Conditions` applies to every search mode that lists projects: current status, donor, donor
 * group, request date range and a name or number search. The project rows are joined as `m` (`op_microproject`) unless a mode lists
 * projects themselves and overrides `projectAlias()`.
 */
trait FiltersProjects
{
    protected function projectAlias(): string
    {
        return 'm';
    }

    /** Correlated SQL for the state catalog id of the project now: flagged current, else the newest row. */
    protected function currentStateSql(): string
    {
        $m = $this->projectAlias();
        $project = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $state = $this->tables->relation('microproject_state', 'State_ID');
        $table = $this->tables->table('microproject_state')['name'];

        return 'select ms.'.$state['resolved_column'].' from '.$table.' ms where ms.'.$project['resolved_column']." = $m.id and ms.".$project['state_column']."= 'resolved'"
            ." and ms.city_id = $m.city_id and ms.source_id = $m.source_id and ms.".$state['state_column']."= 'resolved'"
            .' order by case when ms.c_Current_State = 1 then 0 else 1 end, ms.c_Date desc, ms.id desc limit 1';
    }

    /** Project classification lists of the filter form: filter key → [catalog table, project relationship, name column]. */
    public const CLASSIFICATION = [
        'fund' => ['fundtype', 'MP_FundType_ID'], 'type' => ['microprojecttype', 'MP_Type_ID'], 'subtype' => ['microprojectsubtype', 'MP_SubType_ID'],
        'category' => ['category', 'Category_ID'], 'subcategory' => ['subcategory', 'SubCategory_ID'], 'career' => ['career', 'Career_ID'],
    ];

    /**
     * The desktop's fund type, project type, sub type, category, sub category and career lists (several names may be chosen; a project
     * matches any of them) and its partnership choice ("individual": one person, "partnership": more than one).
     */
    protected function applyClassificationFilters(Builder $query, array $cityIds, array $filters): void
    {
        $m = $this->projectAlias();
        foreach (self::CLASSIFICATION as $key => [$table, $relation]) {
            $names = array_values(array_filter((array) ($filters[$key] ?? []), fn ($name) => is_string($name) && $name !== ''));
            if ($names === []) {
                continue;
            }
            $matches = $this->catalog($table, $cityIds)->filter(fn ($row) => in_array((string) ($row->c_Name ?? ''), $names, true))->pluck('id')->all();
            $query->whereIn($m.'.'.$this->tables->relation('microproject', $relation)['resolved_column'], $matches === [] ? [0] : $matches);
        }
        match ($filters['partnership'] ?? '') {
            'individual' => $query->where($m.'.c_Partnership', 1),
            'partnership' => $query->where($m.'.c_Partnership', '>', 1),
            default => null,
        };
    }

    protected function applyProjectFilters(Builder $query, array $cityIds, array $filters): void
    {
        $m = $this->projectAlias();
        $ref = fn (string $key) => $this->tables->relation('microproject', $key);
        $ids = fn (string $table, string $column, string $name) => $this->catalog($table, $cityIds)->filter(fn ($row) => (string) ($row->{$column} ?? '') === $name)->pluck('id')->all();
        if (($filters['state'] ?? '') !== '') {
            $matches = $ids('state', 'c_Name_en', (string) $filters['state']);
            $query->whereRaw('('.$this->currentStateSql().') in ('.implode(',', array_fill(0, max(1, count($matches)), '?')).')', $matches === [] ? [0] : $matches);
        }
        foreach (['donor' => ['donor', 'MP_Donor'], 'group' => ['donorgroup', 'DonorGroup_ID']] as $key => [$table, $relation]) {
            if (($filters[$key] ?? '') !== '') {
                $matches = $ids($table, 'c_Name', (string) $filters[$key]);
                $query->whereIn($m.'.'.$ref($relation)['resolved_column'], $matches === [] ? [0] : $matches);
            }
        }
        foreach ([['request_from', '>=', 'c_MP_DateOfRequest'], ['request_to', '<=', 'c_MP_DateOfRequest']] as [$key, $operator, $column]) {
            if (($filters[$key] ?? '') !== '') {
                $query->where($m.'.'.$column, $operator, $filters[$key]);
            }
        }
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search, $m) {
                $q->whereRaw("$m.c_MP_Name LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($search, 0, 200)).'%']);
                if (ctype_digit($search) && strlen($search) <= 10) {
                    $q->orWhere("$m.c_MP_ID", (int) $search);
                }
            });
        }
    }
}
