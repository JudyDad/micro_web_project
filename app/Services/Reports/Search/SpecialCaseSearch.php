<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Special Cases" (`Search_Form.bind_SpecialCases`): every special-case review with its project and people (one line per linked person, as
 * in the desktop's joins). The reviewer's name is not shown: the desktop `user` table is not part of the web data model.
 */
final class SpecialCaseSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    public function key(): string
    {
        return 'special-cases';
    }

    protected function capabilities(): array
    {
        return ['special_cases.view', 'applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return ['City', 'Project number', 'Beneficiary', 'Father', 'Career', 'Category', 'Sub category', 'Review date', 'Review type', 'Reason', 'Description', 'Action'];
    }

    public function numeric(): array
    {
        return [1];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $project = $this->relationOf('specialcase', 'MicroProject_ID');
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $query = $this->tables->query('specialcase')->whereIn('p.city_id', $cityIds)
            ->join('op_microproject as m', fn ($j) => $this->scoped('m', 'p')($j->on('m.id', '=', 'p.'.$project['resolved_column'])->where('p.'.$project['state_column'], 'resolved')))
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'm')($j->on('l.'.$linkProject['resolved_column'], '=', 'm.id')->where('l.'.$linkProject['state_column'], 'resolved')))
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'l')($j->on('pe.id', '=', 'l.'.$linkPerson['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->addSelect(['m.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FatherName', 'p.'.$this->relationOf('specialcase', 'Type_ID')['resolved_column'].' as type_id'])
            ->whereRaw('('.$this->currentStateSql().') is not null');
        $this->selectProjectRefs($query, 'm', ['career_id' => 'Career_ID', 'category_id' => 'Category_ID', 'subcategory_id' => 'SubCategory_ID']);
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderByDesc('p.c_Date')->orderBy('p.city_id')->orderBy('p.id')->orderBy('l.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $types = $this->catalog('specialcasetype', $cities);
        $careers = $this->catalog('career', $cities);
        $categories = $this->catalog('category', $cities);
        $subs = $this->catalog('subcategory', $cities);

        return $rows->map(fn ($row) => [
            $row->city_name_en, $row->project_number, $this->personName($row), $row->c_P_FatherName, $this->nameOf($careers, $row->career_id), $this->nameOf($categories, $row->category_id),
            $this->nameOf($subs, $row->subcategory_id), $this->day($row->c_Date ?? null), $this->nameOf($types, $row->type_id), $row->c_Reason, $row->c_Description, $row->c_Action,
        ])->all();
    }
}
