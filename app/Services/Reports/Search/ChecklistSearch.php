<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Check Lists" (`Search_Form.bind_CheckList`, `CheckList.BuildChecklistReportQuery`): one line per checklist with its project, the home and
 * project visits, the professional test and training, the team and committee dates and the opinions of the key people, team and
 * committee. A checklist belongs to the beneficiary, so a beneficiary with several projects appears once for each.
 *
 * Not shown, because the data is not in the web data model: the examiner and the rejection-message sender (desktop `user` names) and the
 * desktop's total marks (its `need_evaluation` view). The project-visit second visitor is the checklist's project-visit second visitor;
 * the desktop's SQL reads the home-visit one there by mistake.
 */
final class ChecklistSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    private const FLAGS = [
        'home' => ['Done', 'Not done'], 'need' => ['Needed', 'Not needed'], 'yesno' => ['Yes', 'No'], 'reject' => ['Sent', 'Not sent'],
    ];

    public function key(): string
    {
        return 'checklists';
    }

    protected function capabilities(): array
    {
        return ['assessments.view', 'applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return [
            'City', 'Project number', 'Beneficiary', 'Father', 'Career', 'Category', 'Sub category', 'Project sub type', 'Project status', 'Years of experience',
            'Home visit', 'Home visit date', 'Home visit time', 'Home visitor 1', 'Home visitor 2', 'Home visit notes',
            'Project visit', 'Project visit date', 'Project visit time', 'Project visitor 1', 'Project visitor 2', 'Project visit notes', 'Visits report',
            'Needs professional test', 'Reason for the test', 'Professional test done', 'Test date', 'Test notes', 'Test result',
            'Professional training', 'Trainer (professional)', 'Place (professional)', 'Training start', 'Training end', 'Training notes (professional)', 'Training result (professional)',
            'Management training', 'Trainer (management)', 'Place (management)', 'Management training date', 'Management training result', 'General notes',
            'Rejection message', 'Rejection message date', 'Key people', 'Key people opinion', 'Team meeting date', 'Team members', 'Team opinion', 'Committee meeting date', 'Committee members', 'Committee opinion',
        ];
    }

    public function numeric(): array
    {
        return [1, 9, 44, 47, 50];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $person = $this->relationOf('checklist', 'Person_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $query = $this->tables->query('checklist')->whereIn('p.city_id', $cityIds)
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'p')($j->on('pe.id', '=', 'p.'.$person['resolved_column'])->where('p.'.$person['state_column'], 'resolved')))
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'p')($j->on('l.'.$linkPerson['resolved_column'], '=', 'p.'.$person['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->leftJoin('op_microproject as m', fn ($j) => $this->scoped('m', 'l')($j->on('m.id', '=', 'l.'.$linkProject['resolved_column'])->where('l.'.$linkProject['state_column'], 'resolved')))
            ->addSelect(['m.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FatherName', 'l.c_P_WorkYears as work_years', 'p.'.$person['resolved_column'].' as person_id'])
            ->whereRaw('('.$this->currentStateSql().') is not null')
            ->selectRaw('('.$this->currentStateSql().') as current_state_id');
        foreach (['hv1' => 'HVisit_Visitor1_ID', 'hv2' => 'HVisit_Visitor2_ID', 'pv1' => 'PVisit_Visitor1_ID', 'pv2' => 'PVisit_Visitor2_ID'] as $alias => $key) {
            $query->addSelect('p.'.$this->relationOf('checklist', $key)['resolved_column'].' as '.$alias);
        }
        $this->selectProjectRefs($query, 'm', ['career_id' => 'Career_ID', 'category_id' => 'Category_ID', 'subcategory_id' => 'SubCategory_ID', 'subtype_id' => 'MP_SubType_ID']);
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderByDesc('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->orderBy('l.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $catalogs = ['career' => $this->catalog('career', $cities), 'category' => $this->catalog('category', $cities), 'sub' => $this->catalog('subcategory', $cities),
            'subtype' => $this->catalog('microprojectsubtype', $cities), 'state' => $this->catalog('state', $cities), 'member' => $this->catalog('checklist_member', $cities)];
        $memberPerson = $this->relationOf('person_checklistmembers', 'Person_ID');
        $memberRef = $this->relationOf('person_checklistmembers', 'Member_ID')['resolved_column'];
        $members = $this->tables->query('person_checklistmembers')->whereIn('p.'.$memberPerson['resolved_column'], $rows->pluck('person_id')->filter()->unique()->all())
            ->where('p.'.$memberPerson['state_column'], 'resolved')->orderBy('p.id')->get()->groupBy(fn ($m) => (int) $m->{$memberPerson['resolved_column']});
        $flag = fn ($value, string $set) => match ((string) $value) {
            '1' => self::FLAGS[$set][0], '0' => self::FLAGS[$set][1], default => ''
        };
        $opinion = fn ($value) => match ((string) $value) {
            '0' => 'Against', '1' => 'For', default => ''
        };

        return $rows->map(function ($row) use ($catalogs, $members, $memberRef, $flag, $opinion) {
            $mine = $members->get((int) $row->person_id, collect());
            $member = fn ($id) => $this->nameOf($catalogs['member'], $id);
            $location = fn (string $where) => $mine->filter(fn ($m) => strcasecmp((string) $m->c_Location, $where) === 0);
            $opinions = fn (string $where) => $location($where)->map(function ($m) use ($memberRef, $member, $opinion, $where) {
                $name = $member($m->{$memberRef});
                $notes = (string) ($m->c_Notes ?? '');
                if ($where === 'k') {
                    return implode(' - ', [$m->c_NameNotes, $name, $m->c_RoleNotes, $notes]);
                }

                return $name.' - '.$opinion($m->c_Opinion).($notes === '' ? '' : ' - '.$notes);
            })->implode("\n");

            return [
                $row->city_name_en, $row->project_number, $this->personName($row), $row->c_P_FatherName, $this->nameOf($catalogs['career'], $row->career_id), $this->nameOf($catalogs['category'], $row->category_id),
                $this->nameOf($catalogs['sub'], $row->subcategory_id), $this->nameOf($catalogs['subtype'], $row->subtype_id), $this->stateName($catalogs['state']->get((int) ($row->current_state_id ?? 0))), $row->work_years,
                $flag($row->c_HasHomeVisit, 'home'), $this->day($row->c_HVisit_Date ?? null), $row->c_HVisit_Time, $member($row->hv1), $member($row->hv2), $row->c_HVisit_Note,
                $flag($row->c_HasProjectVisit, 'home'), $this->day($row->c_PVisit_Date ?? null), $row->c_PVisit_Time, $member($row->pv1), $member($row->pv2), $row->c_PVisit_Note, $row->c_VisitsReport,
                $flag($row->c_NeedProTest, 'need'), $row->c_NeedProTest_Note, $flag($row->c_HasProTest, 'yesno'), $this->day($row->c_ProTest_Date ?? null), $row->c_ProTest_Note, $row->c_ProTest_Result,
                $flag($row->c_HasProTrain, 'yesno'), $row->c_ProTrain_Trainer, $row->c_ProTrain_Place, $this->day($row->c_ProTrain_BDate ?? null), $this->day($row->c_ProTrain_EDate ?? null), $row->c_ProTrain_Note, $row->c_ProTrain_Result,
                $flag($row->c_HasMngTrain, 'yesno'), $row->c_MngTrain_Trainer, $row->c_MngTrain_Place, $this->day($row->c_MngTrain_Date ?? null), $row->c_MngTrain_Result, $row->c_Notes,
                $flag($row->c_RejectMsg, 'reject'), $this->day($row->c_RejectMsgDate ?? null),
                $location('k')->count(), $opinions('k'), $this->day($row->c_TeamDate ?? null), $location('t')->count(), $opinions('t'), $this->day($row->c_CommityDate ?? null), $location('c')->count(), $opinions('c'),
            ];
        })->all();
    }
}
