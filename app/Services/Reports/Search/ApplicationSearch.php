<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Applications" (`Search_Form.bind_Applications`): one line per project and linked person (applicant or partner), with the project, its
 * status and loan, and the person's details. A project with several loans appears once for each, as in the desktop's joins.
 *
 * Not shown: the spouse and the two child counts (subqueries over the family rows in the desktop).
 */
final class ApplicationSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    protected function projectAlias(): string
    {
        return 'p';
    }

    public function key(): string
    {
        return 'applications';
    }

    protected function capabilities(): array
    {
        return ['applications.view', 'people.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return [
            'City', 'Project number', 'Beneficiary', 'Beneficiary (English)', 'Father', 'Mother', 'Career', 'Career (English)', 'Project description', 'Specialty', 'Category', 'Sub category', 'Project type', 'Project sub type',
            'Vehicle plate', 'Fund type', 'Request date', 'Project status', 'Status date', 'Status count', 'Loan date', 'Loan amount', 'Donor', 'Group', 'Waiting months', 'Project replaced',
            'Date of birth', 'Parish', 'Priest 1', 'Priest 2', 'Home area', 'Address', 'Registration place', 'National number', 'Mobile', 'Mobile 2', 'Family members', 'Members at home',
            'Military service', 'Discharge date', 'Military note', 'Service during the war', 'Sex', 'Marital status', 'Project area', 'Project location', 'Applicant role', 'Application type',
        ];
    }

    public function numeric(): array
    {
        return [1, 19, 21, 24, 36, 37];
    }

    /** The current-state SQL with another column selected from the winning state row. */
    private function stateRow(string $select): string
    {
        return preg_replace('/\Aselect \S+/', 'select '.$select, $this->currentStateSql());
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $loanProject = $this->tables->relation('loan', 'MicroProject_ID');
        $personRef = fn (string $key) => 'pe.'.$this->tables->relation('person', $key)['resolved_column'];
        $statesTable = $this->tables->table('microproject_state')['name'];
        $stateProject = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $query = $this->tables->query('microproject')->whereIn('p.city_id', $cityIds)
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'p')($j->on('l.'.$linkProject['resolved_column'], '=', 'p.id')->where('l.'.$linkProject['state_column'], 'resolved')))
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'l')($j->on('pe.id', '=', 'l.'.$linkPerson['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->leftJoin('op_loan as ln', fn ($j) => $this->scoped('ln', 'p')($j->on('ln.'.$loanProject['resolved_column'], '=', 'p.id')->where('ln.'.$loanProject['state_column'], 'resolved')))
            ->addSelect([
                'p.c_MP_ID as project_number', 'l.c_PersonType as person_type', 'ln.c_Loan_DateTaken as loan_date', 'ln.c_Loan_Amount as loan_amount',
                'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FullName_en', 'pe.c_P_FatherName', 'pe.c_P_MotherName', 'pe.c_P_DOB', 'pe.c_P_HomeAddress', 'pe.c_P_RegistrationPlace', 'pe.c_P_NationalNumber',
                'pe.c_P_Mobile', 'pe.c_P_Mobile2', 'pe.c_P_NumOfMembers', 'pe.c_P_NumAtHome', 'pe.c_P_MilitaryService', 'pe.c_P_MilitaryService_Date', 'pe.c_P_MilitaryService_Note', 'pe.c_P_IsMilitaryInWar', 'pe.c_P_Sex',
                $personRef('P_Parish_ID').' as parish_id', $personRef('P_Priest_ID').' as priest1_id', $personRef('P_Priest2_ID').' as priest2_id', $personRef('Street_ID').' as home_street_id', $personRef('P_MaritalStatus_ID').' as marital_id',
            ])
            ->selectRaw('('.$this->currentStateSql().') as current_state_id')
            ->selectRaw('('.$this->stateRow('ms.c_Date').') as state_date')
            ->selectRaw('(select count(*) from '.$statesTable.' ms2 where ms2.'.$stateProject['resolved_column'].' = p.id and ms2.city_id = p.city_id and ms2.source_id = p.source_id) as state_count')
            ->whereRaw('('.$this->currentStateSql().') is not null');
        $this->selectProjectRefs($query, 'p', [
            'career_id' => 'Career_ID', 'category_id' => 'Category_ID', 'subcategory_id' => 'SubCategory_ID', 'type_id' => 'MP_Type_ID', 'subtype_id' => 'MP_SubType_ID', 'fund_id' => 'MP_FundType_ID',
            'donor_id' => 'MP_Donor', 'group_id' => 'DonorGroup_ID', 'work_street_id' => 'MP_Street_ID',
        ]);
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('p.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->orderBy('l.id')->orderBy('ln.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $c = [];
        foreach (['career', 'category', 'subcategory', 'microprojecttype', 'microprojectsubtype', 'fundtype', 'donor', 'donorgroup', 'state', 'parish', 'priest', 'street', 'maritalstatus'] as $table) {
            $c[$table] = $this->catalog($table, $cities);
        }
        $visitProject = $this->relationOf('visit', 'MicroProject_ID');
        $plates = $this->tables->query('visit')->whereIn('p.'.$visitProject['resolved_column'], $rows->pluck('id')->unique()->all())->where('p.'.$visitProject['state_column'], 'resolved')
            ->where('p.c_Kind', 'V-op')->orderBy('p.id')->get()->unique(fn ($v) => (int) $v->{$visitProject['resolved_column']})->keyBy(fn ($v) => (int) $v->{$visitProject['resolved_column']});

        return $rows->map(function ($row) use ($c, $plates) {
            $request = $this->day($row->c_MP_DateOfRequest ?? null);
            $loan = $this->day($row->loan_date ?? null);
            $waiting = $request !== null && $loan !== null ? (int) CarbonImmutable::parse($request)->diffInMonths(CarbonImmutable::parse($loan), false) : '';
            $stage = match ($row->c_ApplicationState) {
                'zero' => 'Preliminary', 'project' => 'Full', default => (string) $row->c_ApplicationState
            };

            return [
                $row->city_name_en, $row->project_number, $this->personName($row), $row->c_P_FullName_en, $row->c_P_FatherName, $row->c_P_MotherName, $this->nameOf($c['career'], $row->career_id), $this->nameOf($c['career'], $row->career_id, 'c_Name_en'),
                $row->c_MP_Name, $row->c_MP_Speciality, $this->nameOf($c['category'], $row->category_id), $this->nameOf($c['subcategory'], $row->subcategory_id), $this->nameOf($c['microprojecttype'], $row->type_id),
                $this->nameOf($c['microprojectsubtype'], $row->subtype_id), $plates->get((int) $row->id)->c_VehicleNumber ?? '', $this->nameOf($c['fundtype'], $row->fund_id), $request,
                $this->stateName($c['state']->get((int) ($row->current_state_id ?? 0))), $this->day($row->state_date ?? null), (int) $row->state_count, $loan, $row->loan_amount === null ? 0 : $row->loan_amount,
                $this->nameOf($c['donor'], $row->donor_id), $this->nameOf($c['donorgroup'], $row->group_id), $waiting, (string) ($row->c_IsContentUpdated ?? '') === '0' ? 'No' : 'Yes',
                $this->day($row->c_P_DOB ?? null) ?? '1900', $this->nameOf($c['parish'], $row->parish_id, 'c_Name_en'), $this->nameOf($c['priest'], $row->priest1_id, 'c_Priest_Name'), $this->nameOf($c['priest'], $row->priest2_id, 'c_Priest_Name'),
                $this->nameOf($c['street'], $row->home_street_id), $row->c_P_HomeAddress, $row->c_P_RegistrationPlace, $row->c_P_NationalNumber, $row->c_P_Mobile, $row->c_P_Mobile2,
                (int) ($row->c_P_NumOfMembers ?? 0), (int) ($row->c_P_NumAtHome ?? 0), $row->c_P_MilitaryService, $this->day($row->c_P_MilitaryService_Date ?? null), $row->c_P_MilitaryService_Note,
                match ((string) $row->c_P_IsMilitaryInWar) {
                    '0' => 'No', '1' => 'Yes', default => 'N/A'
                }, $row->c_P_Sex, $this->nameOf($c['maritalstatus'], $row->marital_id),
                $this->nameOf($c['street'], $row->work_street_id), $row->c_MP_AddressAfterFund, $row->person_type, $stage,
            ];
        })->all();
    }
}
