<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/** Helpers shared by the search modes that list projects with their people and catalog names. */
trait ReadsProjects
{
    /** Joins `$alias` to `$parent` within the same city and source. */
    protected function scoped(string $alias, string $parent): \Closure
    {
        return fn ($join) => $join->on($alias.'.city_id', '=', $parent.'.city_id')->on($alias.'.source_id', '=', $parent.'.source_id');
    }

    /** The relationship of `$table` whose child column is `$legacy`, matched without regard to case. */
    protected function relationOf(string $table, string $legacy): array
    {
        foreach ($this->tables->relationships($table) as $relation) {
            if (strcasecmp($relation['child_columns'][0], $legacy) === 0) {
                return $relation;
            }
        }
        abort(500, "No relationship $table.$legacy");
    }

    /**
     * Adds the project's catalog links to the select as `<alias>_id` columns.
     *
     * @param  array<string, string>  $keys  select alias => microproject relationship key
     */
    protected function selectProjectRefs(Builder $query, string $project, array $keys): void
    {
        foreach ($keys as $alias => $key) {
            $query->addSelect($project.'.'.$this->tables->relation('microproject', $key)['resolved_column'].' as '.$alias);
        }
    }

    /** Catalog name by entity id, or by legacy number when the import left the link unresolved. */
    protected function nameOf(Collection $catalog, mixed $id, string $column = 'c_Name'): string
    {
        return $id === null ? '' : (string) ($catalog->get((int) $id)->{$column} ?? '');
    }

    protected function day(mixed $value): ?string
    {
        $text = $value === null ? '' : substr((string) $value, 0, 10);

        return $text === '' || str_starts_with($text, '0000') ? null : $text;
    }

    protected function personName(object $person, bool $withFather = false): string
    {
        $name = trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? ''));

        return $withFather ? trim($name.' ابن/ة '.($person->c_P_FatherName ?? '')) : $name;
    }

    /** @return array<int, array{names: string, mobile: string}> project id → every linked person's name ("first last ابن/ة father") and the first mobile */
    protected function peopleOfProjects(array $projectIds): array
    {
        if ($projectIds === []) {
            return [];
        }
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->whereIn('p.'.$linkProject['resolved_column'], $projectIds)->where('p.'.$linkPerson['state_column'], 'resolved')->orderBy('p.id')->get();
        $persons = $this->tables->query('person')->whereIn('p.id', $links->pluck($linkPerson['resolved_column'])->unique()->all())->get()->keyBy('id');
        $result = [];
        foreach ($links as $link) {
            $person = $persons->get((int) $link->{$linkPerson['resolved_column']});
            if ($person === null) {
                continue;
            }
            $project = (int) $link->{$linkProject['resolved_column']};
            $name = $this->personName($person, true);
            $result[$project]['names'] = isset($result[$project]['names']) ? $result[$project]['names'].', '.$name : $name;
            if (($result[$project]['mobile'] ?? '') === '') {
                $result[$project]['mobile'] = (string) ($person->c_P_Mobile ?? '');
            }
        }

        return $result;
    }
}
