<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "Educations" (`Search_Form.bind_Educations`): every family member with relation and education, under the project of the family's
 * provider. As in the desktop, the education comes from the member's own family row or, for the provider's own row, from the
 * provider's education rows (a provider with several education rows appears once for each).
 */
final class EducationSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    public function key(): string
    {
        return 'educations';
    }

    protected function capabilities(): array
    {
        return ['people.view', 'applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return ['City', 'Project number', 'Name', 'Relation', 'Education level', 'Degree', 'Specialization'];
    }

    public function numeric(): array
    {
        return [1];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $person = $this->relationOf('person_family', 'Person_ID')['resolved_column'];
        $provider = $this->relationOf('person_family', 'P_Provider_ID');
        $level = $this->relationOf('person_family', 'EducationLevel_ID')['resolved_column'];
        $education = $this->relationOf('person_family', 'Education_ID')['resolved_column'];
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $edPerson = $this->relationOf('person_education', 'Person_ID');
        $query = $this->tables->query('person_family')->whereIn('p.city_id', $cityIds)
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'p')($j->on('pe.id', '=', 'p.'.$person)))
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'p')($j->on('l.'.$linkPerson['resolved_column'], '=', 'p.'.$provider['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->leftJoin('op_microproject as m', fn ($j) => $this->scoped('m', 'l')($j->on('m.id', '=', 'l.'.$linkProject['resolved_column'])->where('l.'.$linkProject['state_column'], 'resolved')))
            ->leftJoin('op_person_education as ed', fn ($j) => $this->scoped('ed', 'p')($j->on('ed.'.$edPerson['resolved_column'], '=', 'p.'.$provider['resolved_column'])->whereColumn('p.'.$person, 'p.'.$provider['resolved_column'])))
            ->addSelect([
                'm.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'p.'.$this->relationOf('person_family', 'Relation_ID')['resolved_column'].' as relation_id',
                DB::raw('COALESCE(p.'.$level.', ed.'.$this->relationOf('person_education', 'Level_ID')['resolved_column'].') as level_id'),
                DB::raw('COALESCE(p.'.$education.', ed.'.$this->relationOf('person_education', 'Education_ID')['resolved_column'].') as education_id'),
            ])->whereRaw('('.$this->currentStateSql().') is not null');
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderByDesc('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->orderBy('ed.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $relations = $this->catalog('relation', $cities);
        $levels = $this->catalog('educationlevel', $cities);
        $educations = $this->catalog('education', $cities);
        $types = $this->catalog('educationtype', $cities);
        $typeRef = $this->tables->relation('education', 'Type_ID')['resolved_column'];

        return $rows->map(function ($row) use ($relations, $levels, $educations, $types, $typeRef) {
            $education = $educations->get((int) ($row->education_id ?? 0));

            return [
                $row->city_name_en, $row->project_number, $this->personName($row), $this->nameOf($relations, $row->relation_id), $this->nameOf($levels, $row->level_id),
                $education === null ? '' : $this->nameOf($types, $education->{$typeRef} ?? null), (string) ($education->c_Name ?? ''),
            ];
        })->all();
    }
}
