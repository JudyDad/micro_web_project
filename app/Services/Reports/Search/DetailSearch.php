<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * The desktop's twelve "detail table" search modes (`bind_person_tables`, `bind_project_tables`): the rows of one detail table (assets,
 * losses, materials, …) listed with the project they belong to.
 *
 * - "person" modes list a person's detail rows with the person's name and relation to the beneficiary and the project of the family's
 *   provider (`person_family.P_Provider_ID`), as the desktop joins them; a person with several family links appears once for each.
 * - "project" modes list a project's detail rows with the project's linked people.
 * Only projects with a current lifecycle status appear (the desktop's `Current_State = 1`). Catalog values are shown by name.
 *
 * A column is `[heading, legacy column]` for a stored value, `[heading, legacy column, 'date']` for a date, or
 * `[heading, legacy column, 'catalog', catalog table, name column]` for a catalog name.
 */
final class DetailSearch extends SearchMode
{
    use FiltersProjects;

    public const DEFINITIONS = [
        'aid-sources' => ['person', 'person_aidsource', [['Funding body', 'AidSource_ID', 'catalog', 'aidsource', 'c_Name'], ['Year of benefit', 'DateYear'], ['Amount', 'Amount'], ['Project type', 'ProjectType'], ['Items benefited', 'Notes']]],
        'assets' => ['person', 'person_asset', [['Asset type', 'Asset_Type_ID', 'catalog', 'assettype', 'c_Name'], ['Year of purchase', 'Year'], ['Asset kind', 'Asset_ID', 'catalog', 'asset', 'c_Name'], ['Description', 'Description'], ['Benefit', 'Asset_Benefits_ID', 'catalog', 'assetbenefits', 'c_Name'], ['Notes', 'Note']]],
        'courses' => ['person', 'person_course', [['Course type', 'SubCategory_ID', 'catalog', 'subcategory', 'c_Name'], ['Course name', 'Name'], ['Institute', 'Place'], ['Funding body', 'Donor'], ['Course date', 'Date', 'date']]],
        'goals' => ['person', 'person_goals', [['Purpose of the loan', 'Goal_ID', 'catalog', 'goal', 'c_Name']]],
        'relief-aids' => ['person', 'person_help', [['Aid source', 'AidSource_ID', 'catalog', 'aidsource', 'c_Name'], ['Aid type', 'Aid_ID', 'catalog', 'aid', 'c_Name'], ['Aid period', 'Aidperiod_ID', 'catalog', 'aidperiod', 'c_Name'], ['Notes', 'Notes']]],
        'illness' => ['person', 'person_illness', [['Health condition', 'Illness_ID', 'catalog', 'illness', 'c_Name'], ['Description', 'Note'], ['Medical aid', 'Has_MedicalAid'], ['Aid source', 'AidSource_ID', 'catalog', 'aidsource', 'c_Name'], ['Aid period', 'AidPeriod_ID', 'catalog', 'aidperiod', 'c_Name'], ['Medical needs', 'MedicalNeeds_ID', 'catalog', 'medicalneeds', 'c_Name']]],
        'losses' => ['person', 'person_loss', [['Loss type', 'Loss_ID', 'catalog', 'loss', 'c_Name'], ['Year', 'Year'], ['Notes', 'Note']]],
        'home-power' => ['person', 'person_powersource', [['Power source', 'PowerSource_ID', 'catalog', 'powersource', 'c_Name'], ['System description', 'Note'], ['Monthly cost', 'Cost'], ['Responsible party', 'Responsability']]],
        'current-works' => ['person', 'person_workexp', [['Work', 'Work_ID', 'catalog', 'work', 'c_W_Name'], ['Work type', 'W_WorkType_ID', 'catalog', 'worktype', 'c_Name'], ['Description', 'W_Description'], ['Start date', 'W_BeginDate', 'date'], ['Premises ownership', 'W_PlaceState'], ['Address', 'W_Place'], ['Employer', 'W_Contact'], ['Profit / salary', 'W_Salary']], ['W_Status', 'yes']],
        'work-experiences' => ['person', 'person_workexp', [['Work', 'Work_ID', 'catalog', 'work', 'c_W_Name'], ['Work type', 'W_WorkType_ID', 'catalog', 'worktype', 'c_Name'], ['Description', 'W_Description'], ['Start date', 'W_BeginDate', 'date'], ['End date', 'W_EndDate', 'date'], ['Premises ownership', 'W_PlaceState'], ['Address', 'W_Place'], ['Employer', 'W_Contact'], ['Cause of stopping', 'W_CauseOfLoseDescription']], ['W_Status', 'no']],
        'materials' => ['project', 'material', [['Item', 'Name'], ['Type', 'Comments'], ['Quantity', 'Amount'], ['Unit price', 'Price']]],
        'project-power' => ['project', 'microproject_powersource', [['Power source', 'PowerSource_ID', 'catalog', 'powersource', 'c_Name'], ['System description', 'Note'], ['Monthly cost', 'Cost'], ['Responsible party', 'Responsability']]],
    ];

    /** @var array<string, string> legacy column → select alias of the resolved catalog id */
    private array $aliases = [];

    public function __construct(AccountAccess $access, AccountAuditTrail $audit, OperationalTables $tables, private string $key = 'assets')
    {
        parent::__construct($access, $audit, $tables);
        abort_unless(isset(self::DEFINITIONS[$key]), 404);
    }

    public function key(): string
    {
        return $this->key;
    }

    protected function capabilities(): array
    {
        return $this->definition()[0] === 'person' ? ['people.view', 'applications.view'] : ['applications.view'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        $lead = $this->definition()[0] === 'person' ? ['City', 'Project number', 'Name', 'Relation'] : ['City', 'Project number', 'Beneficiary', 'Father'];

        return [...$lead, ...array_map(fn ($column) => $column[0], $this->definition()[2])];
    }

    public function numeric(): array
    {
        return [1];
    }

    /** @return array{0: string, 1: string, 2: list<array<int, string>>, 3?: array{0: string, 1: string}} */
    private function definition(): array
    {
        return self::DEFINITIONS[$this->key];
    }

    /** The relation of `$table` whose child column is `$legacy`, matched without regard to case (the sources spell `Aidperiod_ID` and `AidPeriod_ID` both). */
    private function relationOf(string $table, string $legacy): array
    {
        foreach ($this->tables->relationships($table) as $relation) {
            if (strcasecmp($relation['child_columns'][0], $legacy) === 0) {
                return $relation;
            }
        }
        abort(500, "No relationship $table.$legacy");
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        [$kind, $table, $columns] = $this->definition();
        $scope = fn (string $alias, string $parent) => fn ($join) => $join->on($alias.'.city_id', '=', $parent.'.city_id')->on($alias.'.source_id', '=', $parent.'.source_id');
        $rel = fn (string $source, string $key) => $this->relationOf($source, $key);
        $query = $this->tables->query($table)->whereIn('p.city_id', $cityIds);

        if ($kind === 'person') {
            $person = $rel($table, 'Person_ID');
            $familyPerson = $rel('person_family', 'Person_ID');
            $provider = $rel('person_family', 'P_Provider_ID');
            $linkPerson = $rel('person_microproject', 'Person_ID');
            $linkProject = $rel('person_microproject', 'MicroProject_ID');
            // A detail row whose person link the import left unresolved (a city whose source has no declared foreign key for it, e.g. Latakia's
            // person_aidsource) is joined by its legacy person number within the same city, as the desktop does.
            $personId = 'CASE WHEN p.'.$person['state_column']." = 'resolved' THEN p.".$person['resolved_column'].' ELSE (SELECT x.id FROM op_person x WHERE x.city_id = p.city_id AND x.source_id = p.source_id AND x.c_P_ID = p.c_Person_ID LIMIT 1) END';
            $query->leftJoin('op_person as pe', fn ($j) => $scope('pe', 'p')($j->whereRaw('pe.id = ('.$personId.')')))
                ->leftJoin('op_person_family as pf', fn ($j) => $scope('pf', 'pe')($j->on('pf.'.$familyPerson['resolved_column'], '=', 'pe.id')->where('pf.'.$familyPerson['state_column'], 'resolved')))
                ->leftJoin('op_person_microproject as l', fn ($j) => $scope('l', 'pf')($j->on('l.'.$linkPerson['resolved_column'], '=', 'pf.'.$provider['resolved_column'])->where('pf.'.$provider['state_column'], 'resolved')->where('l.'.$linkPerson['state_column'], 'resolved')))
                ->leftJoin('op_microproject as m', fn ($j) => $scope('m', 'l')($j->on('m.id', '=', 'l.'.$linkProject['resolved_column'])->where('l.'.$linkProject['state_column'], 'resolved')))
                ->addSelect(['pf.'.$rel('person_family', 'Relation_ID')['resolved_column'].' as relation_id']);
        } else {
            $project = $rel($table, 'MicroProject_ID');
            $linkProject = $rel('person_microproject', 'MicroProject_ID');
            $linkPerson = $rel('person_microproject', 'Person_ID');
            $query->join('op_microproject as m', fn ($j) => $scope('m', 'p')($j->on('m.id', '=', 'p.'.$project['resolved_column'])->where('p.'.$project['state_column'], 'resolved')))
                ->leftJoin('op_person_microproject as l', fn ($j) => $scope('l', 'm')($j->on('l.'.$linkProject['resolved_column'], '=', 'm.id')->where('l.'.$linkProject['state_column'], 'resolved')))
                ->leftJoin('op_person as pe', fn ($j) => $scope('pe', 'l')($j->on('pe.id', '=', 'l.'.$linkPerson['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')));
        }
        $query->addSelect(['m.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FatherName'])
            ->whereRaw('('.$this->currentStateSql().') is not null'); // the desktop's `Current_State = 1`: only projects with a status

        foreach ($columns as $index => $column) {
            if (($column[2] ?? '') === 'catalog') {
                $this->aliases[$column[1]] = 'cat_'.$index;
                $relation = $this->relationOf($table, $column[1]);
                $query->addSelect(['p.'.$relation['resolved_column'].' as cat_'.$index, 'p.c_'.$column[1].' as leg_'.$index]);
            }
        }
        if (isset($this->definition()[3])) {
            [$statusColumn, $wanted] = $this->definition()[3];
            $query->whereRaw('lower(p.c_'.$statusColumn.') = ?', [$wanted]);
        }
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderByDesc('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id');
    }

    protected function lines(Collection $rows): array
    {
        $columns = $this->definition()[2];
        $cityIds = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $catalogs = [];
        $legacy = [];
        foreach ($columns as $column) {
            if (($column[2] ?? '') === 'catalog') {
                $catalogs[$column[3]] ??= $this->catalog($column[3], $cityIds);
                // Catalog entries by their legacy number within a city, for links the import left unresolved (no declared foreign key in that city's source).
                $key = $column[3] === 'work' ? 'c_W_ID' : 'c_ID';
                $legacy[$column[3]] ??= $catalogs[$column[3]]->mapWithKeys(fn ($entry) => [$entry->city_id.'|'.($entry->{$key} ?? '') => $entry]);
            }
        }
        $relations = $this->definition()[0] === 'person' ? $this->catalog('relation', $cityIds) : collect();

        return $rows->map(function ($row) use ($columns, $catalogs, $legacy, $relations) {
            $lead = $this->definition()[0] === 'person'
                ? [$row->city_name_en, $row->project_number, trim(($row->c_P_FirstName ?? '').' '.($row->c_P_LastName ?? '')), (string) ($relations->get((int) ($row->relation_id ?? 0))->c_Name ?? '')]
                : [$row->city_name_en, $row->project_number, trim(($row->c_P_FirstName ?? '').' '.($row->c_P_LastName ?? '')), $row->c_P_FatherName];
            $cells = [];
            foreach ($columns as $index => $column) {
                $kind = $column[2] ?? '';
                $cells[] = match ($kind) {
                    'catalog' => (string) (($catalogs[$column[3]]->get((int) ($row->{'cat_'.$index} ?? 0)) ?? (isset($row->{'leg_'.$index}) ? $legacy[$column[3]]->get($row->city_id.'|'.$row->{'leg_'.$index}) : null))->{$column[4]} ?? ''),
                    'date' => $this->day($row->{'c_'.$column[1]} ?? null),
                    default => $row->{'c_'.$column[1]} ?? null,
                };
            }

            return [...$lead, ...$cells];
        })->all();
    }

    private function day(mixed $value): ?string
    {
        $text = $value === null ? '' : substr((string) $value, 0, 10);

        return $text === '' || str_starts_with($text, '0000') ? null : $text;
    }
}
