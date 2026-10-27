<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Family Members" (`Search_Form.FamilyMember_bind`): every person with their family book, relation, residence state, work and project
 * link. As in the desktop's left joins, a person with several family links or projects appears once for each combination, and a person
 * with none appears once with those columns blank.
 */
final class FamilySearch extends SearchMode
{
    public function key(): string
    {
        return 'family';
    }

    protected function capabilities(): array
    {
        return ['people.view'];
    }

    public function filters(): array
    {
        return ['q'];
    }

    public function headings(): array
    {
        return ['City', 'Person number', 'Family book number', 'Person', 'Father', 'Mother', 'Relation', 'National number', 'Sex', 'Date of birth', 'Residence state', 'Works', 'Work', 'Project number', 'Family number'];
    }

    public function numeric(): array
    {
        return [1, 2, 13, 14];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $family = fn (string $key) => $this->tables->relation('person_family', $key);
        $link = fn (string $key) => $this->tables->relation('person_microproject', $key);
        $scope = fn (string $alias, string $parent) => fn ($join) => $join->on($alias.'.city_id', '=', $parent.'.city_id')->on($alias.'.source_id', '=', $parent.'.source_id');
        $personState = $this->tables->relation('person', 'P_State_ID');

        $query = $this->tables->query('person')->whereIn('p.city_id', $cityIds)
            ->leftJoin('op_person_family as pf', fn ($j) => $scope('pf', 'p')($j->on('pf.'.$family('Person_ID')['resolved_column'], '=', 'p.id')->where('pf.'.$family('Person_ID')['state_column'], 'resolved')))
            ->leftJoin('op_family as f', fn ($j) => $scope('f', 'pf')($j->on('f.id', '=', 'pf.'.$family('Family_ID')['resolved_column'])->where('pf.'.$family('Family_ID')['state_column'], 'resolved')))
            ->leftJoin('op_person_microproject as l', fn ($j) => $scope('l', 'p')($j->on('l.'.$link('Person_ID')['resolved_column'], '=', 'p.id')->where('l.'.$link('Person_ID')['state_column'], 'resolved')))
            ->leftJoin('op_microproject as m', fn ($j) => $scope('m', 'l')($j->on('m.id', '=', 'l.'.$link('MicroProject_ID')['resolved_column'])->where('l.'.$link('MicroProject_ID')['state_column'], 'resolved')))
            ->addSelect([
                'p.'.$personState['resolved_column'].' as person_state_id', 'pf.'.$family('Relation_ID')['resolved_column'].' as relation_id',
                'pf.c_HasWork as has_work', 'pf.c_Work_Name as work_name', 'pf.c_Family_ID as legacy_family_id', 'f.c_F_Number as family_book', 'm.c_MP_ID as project_number',
            ]);

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($search, 0, 200)).'%';
            $query->where(function (Builder $q) use ($like, $search) {
                foreach (['p.c_P_FirstName', 'p.c_P_LastName', 'p.c_P_FatherName', 'p.c_P_MotherName', 'p.c_P_NationalNumber', 'f.c_F_Number'] as $column) {
                    $q->orWhereRaw($column." LIKE ? ESCAPE '!'", [$like]);
                }
                if (ctype_digit($search) && strlen($search) <= 10) {
                    $q->orWhere('p.c_P_ID', (int) $search);
                }
            });
        }

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('p.city_id')->orderBy('p.id')->orderBy('pf.id')->orderBy('l.id');
    }

    protected function lines(Collection $rows): array
    {
        $cityIds = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $relations = $this->catalog('relation', $cityIds);
        $states = $this->catalog('personstate', $cityIds);
        $english = ! app()->isLocale('ar');
        $label = fn (?object $row) => (string) ($row === null ? '' : ($english ? ($row->c_Name_en ?? $row->c_Name) : ($row->c_Name ?? $row->c_Name_en)));

        return $rows->map(fn ($row) => [
            $row->city_name_en, $row->c_P_ID, $row->family_book, trim(($row->c_P_FirstName ?? '').' '.($row->c_P_LastName ?? '')), $row->c_P_FatherName, $row->c_P_MotherName,
            $label($relations->get((int) ($row->relation_id ?? 0))), $row->c_P_NationalNumber, $row->c_P_Sex, $this->day($row->c_P_DOB ?? null),
            $label($states->get((int) ($row->person_state_id ?? 0))),
            match ((string) ($row->has_work ?? '')) {
                '1' => 'Yes', '0' => 'No', default => ''
            }, $row->work_name, $row->project_number, $row->legacy_family_id,
        ])->all();
    }

    private function day(mixed $value): ?string
    {
        $text = $value === null ? '' : substr((string) $value, 0, 10);

        return $text === '' || str_starts_with($text, '0000') ? null : $text;
    }
}
