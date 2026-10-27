<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "M&E Visits" (`Search_Form.bind_Visits`): one line per project with the date of its opening visit, its five M&E visits and its closing
 * visit. The M&E visits are the beneficiary's (the desktop reads them for a linked person of the project; the web takes the beneficiary,
 * or the first linked person when none is marked as the beneficiary). The desktop's totals panel is not reproduced.
 */
final class VisitDatesSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    protected function projectAlias(): string
    {
        return 'p';
    }

    public function key(): string
    {
        return 'visit-dates';
    }

    protected function capabilities(): array
    {
        return ['visits.view', 'me_visits.view', 'applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return ['City', 'Project number', 'Beneficiaries', 'Career', 'Category', 'Sub category', 'Opening visit', 'M1', 'M2', 'M3', 'M4', 'M5', 'Closing visit'];
    }

    public function numeric(): array
    {
        return [1];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $query = $this->tables->query('microproject')->whereIn('p.city_id', $cityIds)->whereRaw('('.$this->currentStateSql().') is not null');
        $this->selectProjectRefs($query, 'p', ['career_id' => 'Career_ID', 'category_id' => 'Category_ID', 'subcategory_id' => 'SubCategory_ID']);
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('p.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id');
    }

    protected function lines(Collection $rows): array
    {
        $ids = $rows->pluck('id')->all();
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $careers = $this->catalog('career', $cities);
        $categories = $this->catalog('category', $cities);
        $subs = $this->catalog('subcategory', $cities);
        $people = $this->peopleOfProjects($ids);

        $visitProject = $this->relationOf('visit', 'MicroProject_ID');
        $visits = $this->tables->query('visit')->whereIn('p.'.$visitProject['resolved_column'], $ids)->where('p.'.$visitProject['state_column'], 'resolved')->orderBy('p.id')->get()
            ->groupBy(fn ($v) => (int) $v->{$visitProject['resolved_column']});
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->whereIn('p.'.$linkProject['resolved_column'], $ids)->where('p.'.$linkPerson['state_column'], 'resolved')->orderBy('p.id')->get()
            ->groupBy(fn ($l) => (int) $l->{$linkProject['resolved_column']});
        $mePerson = $this->relationOf('mevisit', 'Person_ID');
        $beneficiary = fn (int $project) => ($links->get($project, collect())->first(fn ($l) => $l->c_PersonType === 'مستفيد') ?? $links->get($project, collect())->first())?->{$linkPerson['resolved_column']};
        $persons = $rows->mapWithKeys(fn ($row) => [(int) $row->id => $beneficiary((int) $row->id)]);
        $meVisits = $this->tables->query('mevisit')->whereIn('p.'.$mePerson['resolved_column'], $persons->filter()->unique()->all())->where('p.'.$mePerson['state_column'], 'resolved')->orderBy('p.id')->get()
            ->groupBy(fn ($v) => (int) $v->{$mePerson['resolved_column']});

        return $rows->map(function ($row) use ($careers, $categories, $subs, $people, $visits, $persons, $meVisits) {
            $kind = fn (string $needle) => $visits->get((int) $row->id, collect())->first(fn ($v) => str_contains(strtolower((string) $v->c_Kind), $needle));
            $me = fn (int $number) => $meVisits->get((int) ($persons[(int) $row->id] ?? 0), collect())->first(fn ($v) => (string) $v->c_Number === (string) $number);

            return [
                $row->city_name_en, $row->c_MP_ID, $people[(int) $row->id]['names'] ?? '', $this->nameOf($careers, $row->career_id), $this->nameOf($categories, $row->category_id), $this->nameOf($subs, $row->subcategory_id),
                $this->day($kind('op')->c_Date ?? null), $this->day($me(1)->c_Date ?? null), $this->day($me(2)->c_Date ?? null), $this->day($me(3)->c_Date ?? null), $this->day($me(4)->c_Date ?? null), $this->day($me(5)->c_Date ?? null),
                $this->day($kind('cl')->c_Date ?? null),
            ];
        })->all();
    }
}
