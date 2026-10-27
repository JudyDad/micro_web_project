<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Monitoring Visits (1 to 5)" (`Search_Form.bind_NewVisits`): every M&E visit with its project and people (one line per project the visited
 * person is linked to), net profit, result and evaluation. The visitors' names are not shown (the desktop `user` table is not part of
 * the web data model); their marks are: the first and the last recorded visitor, which are the same when only one is recorded.
 */
final class MeVisitSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    public function key(): string
    {
        return 'me-visits';
    }

    protected function capabilities(): array
    {
        return ['me_visits.view', 'applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return ['City', 'Project number', 'Beneficiary', 'Career', 'Visit date', 'Visit number', 'Visit type', 'Net profit', 'Result', 'Evaluation', 'Visitor 1 mark', 'Visitor 2 mark', 'Donor', 'Category', 'Sub category'];
    }

    public function numeric(): array
    {
        return [1, 5, 7, 8, 10, 11];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $person = $this->relationOf('mevisit', 'Person_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $query = $this->tables->query('mevisit')->whereIn('p.city_id', $cityIds)
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'p')($j->on('pe.id', '=', 'p.'.$person['resolved_column'])->where('p.'.$person['state_column'], 'resolved')))
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'pe')($j->on('l.'.$linkPerson['resolved_column'], '=', 'pe.id')->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->leftJoin('op_microproject as m', fn ($j) => $this->scoped('m', 'l')($j->on('m.id', '=', 'l.'.$linkProject['resolved_column'])->where('l.'.$linkProject['state_column'], 'resolved')))
            ->addSelect(['m.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FatherName'])
            ->whereRaw('('.$this->currentStateSql().') is not null');
        $this->selectProjectRefs($query, 'm', ['career_id' => 'Career_ID', 'category_id' => 'Category_ID', 'subcategory_id' => 'SubCategory_ID', 'donor_id' => 'MP_Donor']);
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->orderBy('l.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $careers = $this->catalog('career', $cities);
        $categories = $this->catalog('category', $cities);
        $subs = $this->catalog('subcategory', $cities);
        $donors = $this->catalog('donor', $cities);
        $visitRef = $this->relationOf('mevisit_user', 'Visit_ID');
        $marks = $this->tables->query('mevisit_user')->whereIn('p.'.$visitRef['resolved_column'], $rows->pluck('id')->unique()->all())->where('p.'.$visitRef['state_column'], 'resolved')
            ->orderBy('p.id')->get()->groupBy(fn ($m) => (int) $m->{$visitRef['resolved_column']});
        $ranges = $this->tables->query('meresultranges')->whereIn('p.city_id', $cities)->orderBy('p.id')->get();

        return $rows->map(function ($row) use ($careers, $categories, $subs, $donors, $marks, $ranges) {
            $visitMarks = $marks->get((int) $row->id, collect());
            $result = $row->c_Result;
            $range = $result === null || $result === '' ? null : $ranges->first(fn ($r) => (int) $r->city_id === (int) $row->city_id && (float) $result >= (float) $r->c_Min && (float) $result <= (float) $r->c_Max);

            return [
                $row->city_name_en, $row->project_number, $this->personName($row, true), $this->nameOf($careers, $row->career_id), $this->day($row->c_Date ?? null), $row->c_Number,
                strtolower((string) $row->c_Kind) === 'v' ? 'Vehicles' : 'Other projects', $row->c_Profit, $result, $range === null ? '' : $range->c_Evaluation.'/'.$range->c_Evaluation_en,
                $visitMarks->first()->c_Mark ?? '', $visitMarks->last()->c_Mark ?? '', $this->nameOf($donors, $row->donor_id), $this->nameOf($categories, $row->category_id), $this->nameOf($subs, $row->subcategory_id),
            ];
        })->all();
    }
}
